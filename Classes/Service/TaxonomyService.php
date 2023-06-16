<?php
namespace Sitegeist\Taxonomy\Service;

use http\Exception\InvalidArgumentException;
use Neos\ContentGraph\DoctrineDbalAdapter\Domain\Repository\ContentSubgraph;
use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\Factory\ContentRepositoryId;
use Neos\ContentRepository\Core\Feature\NodeRenaming\Command\ChangeNodeAggregateName;
use Neos\ContentRepository\Core\Feature\RootNodeCreation\Command\CreateRootNodeAggregateWithNode;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentSubgraphInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindSubtreeFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\Nodes;
use Neos\ContentRepository\Core\Projection\ContentGraph\Subtree;
use Neos\ContentRepository\Core\Projection\ContentGraph\VisibilityConstraints;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeName;
use Neos\ContentRepository\Core\SharedModel\User\UserId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;

use Neos\ContentRepository\Domain\Factory\NodeFactory;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\ContentRepository\Domain\Service\Context;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Fusion\Exception\RuntimeException;
use Neos\Neos\Service\UserService;

/**
 * Class TaxonomyService
 * @package Sitegeist\Taxonomy\Service
 * @Flow\Scope("singleton")
 */
class TaxonomyService
{

    /**
     * @Flow\Inject
     * @var ContentRepositoryRegistry
     */
    protected $crRegistry;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="contentRepository.identifier")
     */
    protected $crIdentifier;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="contentRepository.rootNodeType")
     */
    protected $rootNodeType;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="contentRepository.vocabularyNodeType")
     */
    protected $vocabularyNodeType;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="contentRepository.taxonomyNodeType")
     */
    protected $taxonomyNodeType;

    public function getRootNodeType(): string
    {
        return $this->rootNodeType;
    }

    public function getVocabularyNodeType(): string
    {
        return $this->vocabularyNodeType;
    }

    public function getTaxonomyNodeType(): string
    {
        return $this->taxonomyNodeType;
    }

    public function getContentRepository(): ContentRepository
    {
        return $this->crRegistry->get(ContentRepositoryId::fromString($this->crIdentifier));
    }

    public function findSubgraph(): ContentSubgraphInterface
    {
        $contentRepository = $this->getContentRepository();
        $liveWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(WorkspaceName::forLive());
        $generalizations = $contentRepository->getVariationGraph()->getRootGeneralizations();
        $contentGraph = $contentRepository->getContentGraph();
        $subgraph = $contentGraph->getSubgraph(
            $liveWorkspace->currentContentStreamId,
            reset($generalizations),
            VisibilityConstraints::withoutRestrictions()
        );
        return $subgraph;
    }

    public function findVocabularyForNode(Node $node): Node
    {
        $subgraph = $this->getContentRepository()->getContentGraph()->getSubgraph(
            $node->subgraphIdentity->contentStreamId,
            $node->subgraphIdentity->dimensionSpacePoint,
            $node->subgraphIdentity->visibilityConstraints,
        );

        $parentNode = $node;
        while ($parentNode instanceof Node) {
            if ($parentNode->nodeType->isOfType($this->getVocabularyNodeType())) {
                return $parentNode;
            }
            $parentNode = $subgraph->findParentNode($parentNode->nodeAggregateId);
        }
        throw new \InvalidArgumentException('Node seems to be outside of vocabulary');
    }

    public function findOrCreateRoot(ContentSubgraphInterface $subgraph): Node
    {
        $contentRepository = $this->getContentRepository();
        $liveWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(WorkspaceName::forLive());
        $contentGraph = $contentRepository->getContentGraph();

        try {
            $rootNodeAggregate = $contentGraph->findRootNodeAggregateByType(
                $liveWorkspace->currentContentStreamId,
                NodeTypeName::fromString($this->getRootNodeType())
            );
            return $subgraph->findNodeById($rootNodeAggregate->nodeAggregateId);
        } catch (\Exception) {
            // ignore and create a new root
        }

        $commandResult = $contentRepository->handle(
            new CreateRootNodeAggregateWithNode(
                $liveWorkspace->currentContentStreamId,
                NodeAggregateId::create(),
                NodeTypeName::fromString($this->getRootNodeType())
            )
        );
        $commandResult->block();

        $rootNodeAggregate = $contentGraph->findRootNodeAggregateByType(
            $liveWorkspace->currentContentStreamId,
            NodeTypeName::fromString($this->getRootNodeType())
        );

        return $subgraph->findNodeById($rootNodeAggregate->nodeAggregateId);
    }

    public function findAllVocabularies(ContentSubgraphInterface $subgraph): Nodes
    {
        $root = $this->findOrCreateRoot($subgraph);
        return $subgraph->findChildNodes(
            $root->nodeAggregateId,
            FindChildNodesFilter::create($this->vocabularyNodeType)
        );
    }

    public function findVocabularyByName(ContentSubgraphInterface $subgraph, string $vocabularyName): ?Node
    {
        $vocabularies = $this->findAllVocabularies($subgraph);
        foreach ($vocabularies as $vocabulary) {
            if ($vocabulary->nodeName->value == $vocabularyName) {
                return $vocabulary;
            }
        }
        return null;
    }

    public function findVocabularyOrTaxonomyByPath(ContentSubgraphInterface $subgraph, array $taxonomyPath = []): ?Node
    {
        if (count($taxonomyPath) < 1) {
            return null;
        }
        $vocabularyName = array_shift($taxonomyPath);
        $vocabularyNode = $this->findVocabularyByName($subgraph, $vocabularyName);
        if (!$vocabularyNode) {
            return null;
        }
        $taxonomyNode = $vocabularyNode;
        while (count($taxonomyPath)) {
            $taxonomyName = array_shift($taxonomyPath);
            $taxonomyNode = $subgraph->findChildNodeConnectedThroughEdgeName($taxonomyNode->nodeAggregateId, NodeName::fromString($taxonomyName));
            if (!$taxonomyNode) {
                return null;
            }
        }
        return $taxonomyNode;
    }

    public function findTaxonomySubtree(Node $node): Subtree
    {
        $contentRepository = $this->getContentRepository();
        $subgraph = $contentRepository->getContentGraph()->getSubgraph(
            $node->subgraphIdentity->contentStreamId,
            $node->subgraphIdentity->dimensionSpacePoint,
            $node->subgraphIdentity->visibilityConstraints,
        );

        $vocabularySubtree = $subgraph->findSubtree(
            $node->nodeAggregateId,
            FindSubtreeFilter::create(
                $this->getTaxonomyNodeType()
            )
        );

        return $this->orderSubtreeByNameRecursive($vocabularySubtree);
    }

    private function orderSubtreeByNameRecursive(Subtree $subtree): Subtree
    {
        $children = $subtree->children;
        $children = array_map(
            fn(Subtree $item) => $this->orderSubtreeByNameRecursive($item),
            $children
        );
        usort(
            $children,
            fn(Subtree $a, Subtree $b) => $a->node->nodeName->value <=> $b->node->nodeName->value
        );
        return new Subtree(
            $subtree->level,
            $subtree->node,
            $children
        );
    }
}
