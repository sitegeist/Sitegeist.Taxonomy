<?php
namespace Sitegeist\Taxonomy\Service;

use Neos\ContentGraph\DoctrineDbalAdapter\Domain\Repository\ContentSubgraph;
use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\Factory\ContentRepositoryId;
use Neos\ContentRepository\Core\Feature\RootNodeCreation\Command\CreateRootNodeAggregateWithNode;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\Nodes;
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

    public function getContentRepository(): ContentRepository
    {
        return $this->crRegistry->get(ContentRepositoryId::fromString($this->crIdentifier));
    }

    /**
     * @return Node
     */
    public function getRoot(ContentSubgraph $subgraph): Node
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
    public function getVocabularies(ContentSubgraph $subgraph): Nodes
    {
        $root = $this->getRoot($subgraph);
        return $subgraph->findChildNodes(
            $root->nodeAggregateId,
            FindChildNodesFilter::nodeTypeConstraints($this->vocabularyNodeType)
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
