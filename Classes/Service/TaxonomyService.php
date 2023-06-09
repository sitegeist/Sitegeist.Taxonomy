<?php
namespace Sitegeist\Taxonomy\Service;

use http\Exception\InvalidArgumentException;
use Neos\ContentGraph\DoctrineDbalAdapter\Domain\Repository\ContentSubgraph;
use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\Factory\ContentRepositoryId;
use Neos\ContentRepository\Core\Feature\RootNodeCreation\Command\CreateRootNodeAggregateWithNode;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentSubgraphInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\Nodes;
use Neos\ContentRepository\Core\Projection\ContentGraph\VisibilityConstraints;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
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

    /**
     * @return string
     */
    public function getRootNodeType()
    {
        return $this->rootNodeType;
    }

    /**
     * @return string
     */
    public function getVocabularyNodeType()
    {
        return $this->vocabularyNodeType;
    }

    /**
     * @return string
     */
    public function getTaxonomyNodeType()
    {
        return $this->taxonomyNodeType;
    }

    public function getRootNodeTypeName(): NodeTypeName
    {
        return NodeTypeName::fromString($this->rootNodeType);
    }

    public function getVocabularyNodeTypeName(): NodeTypeName
    {
        return NodeTypeName::fromString($this->vocabularyNodeType);
    }

    public function getTaxonomyNodeTypeName(): NodeTypeName
    {
        return NodeTypeName::fromString($this->taxonomyNodeType);
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

    public function getRoot(ContentSubgraphInterface $subgraph): Node
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
        } catch (\Exception $e) {
            // ignore and create a new root
        }

        $commandResult = $contentRepository->handle(
            new CreateRootNodeAggregateWithNode(
                $liveWorkspace->currentContentStreamId,
                NodeAggregateId::create(),
                NodeTypeName::fromString($this->getRootNodeType()),
                UserId::forSystemUser()
            )
        );
        $commandResult->block();

        $rootNodeAggregate = $contentGraph->findRootNodeAggregateByType(
            $liveWorkspace->currentContentStreamId,
            NodeTypeName::fromString($this->getRootNodeType())
        );

        return $subgraph->findNodeById($rootNodeAggregate->nodeAggregateId);
    }

    /**
     * @return Nodes
     */
    public function getVocabularies(ContentSubgraphInterface $subgraph): Nodes
    {
        $root = $this->getRoot($subgraph);
        return $subgraph->findChildNodes(
            $root->nodeAggregateId,
            FindChildNodesFilter::create($this->vocabularyNodeType)
        );
    }

    /**
     * @param string $vocabularyName
     * @param Context|null $context
     * @param $vocabulary
     */
    public function getVocabulary($vocabularyName, Context $context = null)
    {
        if ($context === null) {
            $context = $this->contextFactory->create();
        }

        $root = $this->getRoot($context);
        return $root->getNode($vocabularyName);
    }

    /**
     * @param string $vocabularyName
     * @param string $taxonomyPath
     * @param Context|null $context
     * @param $vocabulary
     */
    public function getTaxonomy($vocabularyName, $taxonomyPath, Context $context = null)
    {
        $vocabulary = $this->getVocabulary($vocabularyName, $context);
        if ($vocabulary) {
            return $vocabulary->getNode($taxonomyPath);
        }
    }

    /**
     * @param NodeInterface $startingPoint
     * @return array
     */
    public function getTaxonomyTreeAsArray(NodeInterface $startingPoint): array
    {
        $result = [];

        $result['identifier'] = $startingPoint->getIdentifier();
        $result['path'] = $startingPoint->getPath();
        $result['nodeType'] = $startingPoint->getNodeType()->getName();
        $result['label'] = $startingPoint->getLabel();
        $result['title'] = $startingPoint->getProperty('title');
        $result['description'] = $startingPoint->getProperty('description');

        $result['children'] = [];

        foreach ($startingPoint->getChildNodes() as $childNode) {
            $result['children'][] = $this->getTaxonomyTreeAsArray($childNode);
        }
        usort($result['children'], function (array $childA, array $childB) {
            return strcmp(
                $childA['title'] ?: '',
                $childB['title'] ?: ''
            );
        });

        return $result;
    }
}
