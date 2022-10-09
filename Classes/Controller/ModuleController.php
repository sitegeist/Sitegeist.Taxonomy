<?php
namespace Sitegeist\Taxonomy\Controller;

/**
 * This file is part of the Sitegeist.Taxonomies package
 *
 * (c) 2017
 * Martin Ficzel <ficzel@sitegeist.de>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentGraph\DoctrineDbalAdapter\Domain\Repository\ContentSubgraph;
use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Core\DimensionSpace\OriginDimensionSpacePoint;
use Neos\ContentRepository\Core\Factory\ContentRepositoryId;
use Neos\ContentRepository\Core\Feature\NodeCreation\Command\CreateNodeAggregateWithNode;
use Neos\ContentRepository\Core\Feature\NodeCreation\Command\CreateNodeAggregateWithNodeAndSerializedProperties;
use Neos\ContentRepository\Core\Feature\NodeModification\Command\SetNodeProperties;
use Neos\ContentRepository\Core\Feature\NodeModification\Dto\PropertyValuesToWrite;
use Neos\ContentRepository\Core\Feature\NodeRemoval\Command\RemoveNodeAggregate;
use Neos\ContentRepository\Core\Feature\NodeVariation\Command\CreateNodeVariant;
use Neos\ContentRepository\Core\Feature\RootNodeCreation\Command\CreateRootNodeAggregateWithNode;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentSubgraphInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\VisibilityConstraints;
use Neos\ContentRepository\Core\Projection\ContentStream\ContentStreamFinder;
use Neos\ContentRepository\Core\Projection\Workspace\WorkspaceFinder;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeVariantSelectionStrategy;
use Neos\ContentRepository\Core\SharedModel\User\UserId;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Error\Messages\Error;
use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Domain\Model\WorkspaceName;
use Neos\Neos\FrontendRouting\NodeAddress;
use Neos\Neos\FrontendRouting\NodeAddressFactory;
use Sitegeist\Taxonomy\Service\DimensionService;
use Sitegeist\Taxonomy\Service\TaxonomyService;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeServiceInterface;
use Neos\Utility\Arrays;

/**
 * Class ModuleController
 * @package Sitegeist\Monocle\Controller
 */
class ModuleController extends ActionController
{
    /**
     * @var string
     */
    protected $defaultViewObjectName = FusionView::class;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var string
     * @Flow\InjectConfiguration(package="Neos.ContentRepository", path="contentDimensions")
     */
    protected $contentDimensions;

    /**
     * @var TaxonomyService
     * @Flow\Inject
     */
    protected $taxonomyService;

    /**
     * Initialize the view
     *
     * @param  ViewInterface $view
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
       // $this->view->assign('contentDimensionOptions', $this->getContentDimensionOptions());
    }

    public function indexAction(NodeAddress $nodeAddress = null): void
    {
        $subgraph = $this->findSubgraph();
        $rootNode = $this->taxonomyService->getRoot($subgraph);
        $vocabularies = $this->taxonomyService->getVocabularies($subgraph);

        $this->view->assign('taxonomyRoot', $rootNode);
        $this->view->assign('vocabularies', $vocabularies);
        $this->view->assign('dimensionSpacePoint', $rootNode->originDimensionSpacePoint->toDimensionSpacePoint());
        $this->view->assign('dimensionOptions', $this->getContentDimensionOptions($rootNode));

        $flashMessages = $this->controllerContext->getFlashMessageContainer()->getMessagesAndFlush();
        $this->view->assign('flashMessages', $flashMessages);
    }

//    /**
//     * Switch to a modified content context and redirect to the given action
//     *
//     * @param string $targetAction the target action to redirect to
//     * @param string $targetProperty the property in the target action that will accept the node
//     * @param NodeInterface $contextNode the node to adjust the context for
//     * @param array $dimensions array with dimensionName, presetName combinations
//     * @return void
//     */
//    public function changeContextAction($targetAction, $targetProperty, NodeInterface $contextNode, $dimensions = [])
//    {
//        $contextProperties = $contextNode->getContext()->getProperties();
//
//        $newContextProperties = [];
//        foreach ($dimensions as $dimensionName => $presetName) {
//            $newContextProperties['dimensions'][$dimensionName] = $this->getContentDimensionValues(
//                $dimensionName,
//                $presetName
//            );
//            $newContextProperties['targetDimensions'][$dimensionName] = $presetName;
//        }
//        $modifiedContext = $this->contextFactory->create(array_merge($contextProperties, $newContextProperties));
//        $nodeInModifiedContext = $modifiedContext->getNodeByIdentifier($contextNode->getIdentifier());
//
//        $this->redirect($targetAction, null, null, [$targetProperty => $nodeInModifiedContext]);
//    }
//
    /**
     * Prepare all available content dimensions for use in a select box
     *
     * @return array the list of available content dimensions and their presets
     */
    protected function getContentDimensionOptions()
    {
        $contentRepository = $this->taxonomyService->getContentRepository();
        $dimensions = $contentRepository->getContentDimensionSource()->getContentDimensionsOrderedByPriority();
        $result = [];

        foreach ($dimensions as $dimension) {
            $dimensionValues = [];

            foreach ($dimension->values as $value) {
                $dimensionValues[] = [
                    'label' => $value->getConfigurationValue('label'),
                    'value' =>  $value->value,
                ];
            }

            $result[] = [
                'label' => $dimension->getConfigurationValue('label'),
                'dimension' => $dimension->id->id,
                'values' => $dimensionValues
            ];
        }
        return $result;
    }

//
//    /**
//     * Get the content dimension values for a given content dimension and preset
//     *
//     * @param $dimensionName
//     * @param $presetName
//     * @return array the values assiged to the preset identified by $dimensionName and $presetName
//     */
//    protected function getContentDimensionValues($dimensionName, $presetName)
//    {
//        return $this->contentDimensions[$dimensionName]['presets'][$presetName]['values'];
//    }
//
//    /**
//     * @param NodeInterface $node
//     * @return NodeInterface|null
//     */
//    protected function getNodeInDefaultDimensions(NodeInterface $node) : ?NodeInterface
//    {
//        if (!$this->defaultRoot) {
//            $this->defaultRoot = $this->taxonomyService->getRoot();
//        }
//
//        $flowQuery = new FlowQuery([$this->defaultRoot]);
//        $defaultNode = $flowQuery->find('#' . $node->getIdentifier())->get(0);
//        if ($defaultNode && $defaultNode !== $node) {
//            return $defaultNode;
//        } else {
//            return null;
//        }
//    }

    /**
     * @param Node $node
     * @param ContentSubgraphInterface $parents
     * @return array
     */
    public function fetchChildTaxonomies(Node $node, ContentSubgraphInterface $subgraph, array $parentsSoFar = []) : array
    {
        $childTaxonomies = $subgraph->findChildNodes(
            $node->nodeAggregateId,
            FindChildNodesFilter::nodeTypeConstraints($this->taxonomyService->getTaxonomyNodeType())
        );
        $result = [];
        foreach ($childTaxonomies as $childTaxonomy) {
            $result[] = [
                'node' => $childTaxonomy,
                'children' => $this->fetchChildTaxonomies($childTaxonomy, $subgraph, [...$parentsSoFar, $node]),
                'parents' => $parentsSoFar
            ];
        }
        return $result;
    }

    public function vocabularyAction(string $nodeAddress)
    {
        $addressFactory = NodeAddressFactory::create(
            $this->taxonomyService->getContentRepository()
        );

        $nodeAddress = $addressFactory->createFromUriString($nodeAddress);

        $subgraph = $this->findSubgraph();
        $vocabulary = $this->taxonomyService
            ->getContentRepository()
            ->getContentGraph()
            ->getSubgraph(
                $nodeAddress->contentStreamId,
                $nodeAddress->dimensionSpacePoint,
                VisibilityConstraints::withoutRestrictions()
            )
            ->findNodeById($nodeAddress->nodeAggregateId);

        $taxonomies = $this->fetchChildTaxonomies($vocabulary, $subgraph);
        $this->view->assign('vocabulary', $vocabulary);
        $this->view->assign('taxonomies', $taxonomies);
    }


    /**
     * Display a form that allows to create a new vocabulary
     *
     * @param NodeInterface $taxonomyRoot
     * @return void
     */
    public function newVocabularyAction()
    {
        $subgraph = $this->findSubgraph();
        $root = $this->taxonomyService->getRoot($subgraph);
        $this->view->assign('taxonomyRoot', $root);
    }


    public function createVocabularyAction(array $properties): void
    {
        $subgraph = $this->findSubgraph();
        $root = $this->taxonomyService->getRoot($subgraph);
        $contentRepository = $this->taxonomyService->getContentRepository();
        $liveWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(\Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName::forLive());
        $generalizations = $contentRepository->getVariationGraph()->getRootGeneralizations();

        $nodeAggregateId = NodeAggregateId::create();
        $originDimensionSpacePoint = OriginDimensionSpacePoint::fromDimensionSpacePoint(reset($generalizations));
        $commandResult = $contentRepository->handle(
            new CreateNodeAggregateWithNode(
                $liveWorkspace->currentContentStreamId,
                $nodeAggregateId,
                NodeTypeName::fromString($this->taxonomyService->getVocabularyNodeType()),
                $originDimensionSpacePoint,
                UserId::forSystemUser(),
                $root->nodeAggregateId,
                null,
                null,
                PropertyValuesToWrite::fromArray($properties)
            )
        );
        $commandResult->block();

        // add variants
        foreach ($generalizations as $dimensionSpacePoint) {
            $originDimensionSpacePoint2 = OriginDimensionSpacePoint::fromDimensionSpacePoint($dimensionSpacePoint);
            if ($originDimensionSpacePoint->equals($originDimensionSpacePoint2)) {
                continue;
            }

            $commandResult = $contentRepository->handle(
                new CreateNodeVariant(
                    $liveWorkspace->currentContentStreamId,
                    $nodeAggregateId,
                    $originDimensionSpacePoint,
                    $originDimensionSpacePoint2,
                    UserId::forSystemUser()
                )
            );
            $commandResult->block();
        }

        $newVocabularyNode = $subgraph->findNodeById($nodeAggregateId);
        $this->addFlashMessage(
            sprintf('Created vocabulary %s', $newVocabularyNode->getLabel())
        );
        $this->redirect('index');
    }

    /**
     * Show a form that allows to modify the given vocabulary
     *
     * @param NodeAggregateId $vocabularyId
     * @return void
     */
    public function editVocabularyAction(NodeAggregateId $vocabularyId)
    {
        $subgraph = $this->findSubgraph();
        $taxonomyRoot = $this->taxonomyService->getRoot($subgraph);
        $vocabulary = $subgraph->findNodeById($vocabularyId);
        $this->view->assign('taxonomyRoot', $taxonomyRoot);
        $this->view->assign('vocabulary', $vocabulary);
        $this->view->assign('defaultVocabulary', null);
    }

    /**
     * Apply changes to the given vocabulary
     *
     * @param NodeAggregateId $vocabularyId
     * @param array $properties
     * @return void
     */
    public function updateVocabularyAction(NodeAggregateId $vocabularyId, array $properties)
    {
        $subgraph = $this->findSubgraph();
        $taxonomyRoot = $this->taxonomyService->getRoot($subgraph);
        $vocabularyNode = $subgraph->findNodeById($vocabularyId);
        $contentRepository = $this->taxonomyService->getContentRepository();
        $liveWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(\Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName::forLive());

        $commandResult = $contentRepository->handle(
            new SetNodeProperties(
                $liveWorkspace->currentContentStreamId,
                $vocabularyId,
                $vocabularyNode->originDimensionSpacePoint,
                PropertyValuesToWrite::fromArray($properties),
                UserId::forSystemUser()
            )
        );
        $commandResult->block();
        $updatedVocabularyNode = $subgraph->findNodeById($vocabularyId);

        $this->addFlashMessage(
            sprintf('Updated vocabulary %s', $updatedVocabularyNode->getLabel())
        );
        $this->redirect('index', null, null, ['root' => $taxonomyRoot]);
    }

    /**
     * Delete the given vocabulary
     *
     * @param NodeAggregateId $vocabularyId
     * @return void
     * @throws \Exception
     */
    public function deleteVocabularyAction(NodeAggregateId $vocabularyId)
    {
        $subgraph = $this->findSubgraph();
        $vocabularyNode = $subgraph->findNodeById($vocabularyId);

        $contentRepository = $this->taxonomyService->getContentRepository();
        $liveWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(\Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName::forLive());

        $commandResult = $contentRepository->handle(
            new RemoveNodeAggregate(
                $liveWorkspace->currentContentStreamId,
                $vocabularyId,
                $vocabularyNode->originDimensionSpacePoint->toDimensionSpacePoint(),
                NodeVariantSelectionStrategy::STRATEGY_ALL_VARIANTS ,
                UserId::forSystemUser()
            )
        );
        $commandResult->block();
        $this->redirect('index');
    }

    public function newTaxonomyAction(NodeAggregateId $parentNodeAggregateId)
    {
        $this->view->assign('parentNodeAggregateId', $parentNodeAggregateId);
    }

    public function createTaxonomyAction(NodeAggregateId $parentNodeAggregateId, array $properties): void
    {
        $subgraph = $this->findSubgraph();
        $contentRepository = $this->taxonomyService->getContentRepository();
        $liveWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(\Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName::forLive());
        $generalizations = $contentRepository->getVariationGraph()->getRootGeneralizations();

        $nodeAggregateId = NodeAggregateId::create();
        $originDimensionSpacePoint = OriginDimensionSpacePoint::fromDimensionSpacePoint(reset($generalizations));
        $commandResult = $contentRepository->handle(
            new CreateNodeAggregateWithNode(
                $liveWorkspace->currentContentStreamId,
                $nodeAggregateId,
                NodeTypeName::fromString($this->taxonomyService->getTaxonomyNodeType()),
                $originDimensionSpacePoint,
                UserId::forSystemUser(),
                $parentNodeAggregateId,
                null,
                null,
                PropertyValuesToWrite::fromArray($properties)
            )
        );
        $commandResult->block();

        // add variants
        foreach ($generalizations as $dimensionSpacePoint) {
            $originDimensionSpacePoint2 = OriginDimensionSpacePoint::fromDimensionSpacePoint($dimensionSpacePoint);
            if ($originDimensionSpacePoint->equals($originDimensionSpacePoint2)) {
                continue;
            }

            $commandResult = $contentRepository->handle(
                new CreateNodeVariant(
                    $liveWorkspace->currentContentStreamId,
                    $nodeAggregateId,
                    $originDimensionSpacePoint,
                    $originDimensionSpacePoint2,
                    UserId::forSystemUser()
                )
            );
            $commandResult->block();
        }

        $newTaxonomyNode = $subgraph->findNodeById($nodeAggregateId);
        $this->addFlashMessage(
            sprintf('Created taxonomy %s', $newTaxonomyNode->getLabel())
        );
        $this->redirect('index');
    }




//
//    /**
//     * Create a new taxonomy
//     *
//     * @param NodeInterface $parent
//     * @param string $title
//     * @param string $description
//     * @return void
//     */
//    public function createTaxonomyAction(NodeInterface $parent, $title, $description = '')
//    {
//        $nodeTemplate = new NodeTemplate();
//        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($this->taxonomyService->getTaxonomyNodeType()));
//        $nodeTemplate->setName(CrUtitlity::renderValidNodeName($title));
//        $nodeTemplate->setProperty('title', $title);
//        $nodeTemplate->setProperty('description', $description);
//
//        $taxonomy = $parent->createNodeFromTemplate($nodeTemplate);
//
//        $this->addFlashMessage(
//            sprintf('Created taxonomy %s at path %s', $title, $taxonomy->getPath())
//        );
//
//        $flowQuery = new FlowQuery([$taxonomy]);
//        $vocabulary = $flowQuery
//            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
//            ->get(0);
//
//        $this->redirect(
//            'vocabulary',
//            null,
//            null,
//            ['vocabulary' => $vocabulary->getContextPath()]
//        );
//    }
//
//    /**
//     * Display a form that allows to modify the given taxonomy
//     *
//     * @param NodeInterface $taxonomy
//     * @return void
//     */
//    public function editTaxonomyAction(NodeInterface $taxonomy)
//    {
//        $flowQuery = new FlowQuery([$taxonomy]);
//        $vocabulary = $flowQuery
//            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
//            ->get(0);
//
//        $this->view->assign('vocabulary', $vocabulary);
//        $this->view->assign('defaultVocabulary', $this->getNodeInDefaultDimensions($vocabulary));
//
//        $this->view->assign('taxonomy', $taxonomy);
//        $this->view->assign('defaultTaxonomy', $this->getNodeInDefaultDimensions($taxonomy));
//
//    }
//
//    /**
//     * Apply changes to the given taxonomy
//     *
//     * @param NodeInterface $taxonomy
//     * @param string $title
//     * @param string $description
//     * @return void
//     */
//    public function updateTaxonomyAction(NodeInterface $taxonomy, $title, $description = '')
//    {
//        $previousTitle = $taxonomy->getProperty('title');
//        $previousDescription = $taxonomy->getProperty('description');
//
//        if ($previousTitle !== $title) {
//            $taxonomy->setProperty('title', $title);
//        }
//
//        if ($previousDescription !== $description) {
//            $taxonomy->setProperty('description', $description);
//        }
//
//        $this->addFlashMessage(
//            sprintf('Updated taxonomy %s', $taxonomy->getPath())
//        );
//
//        $flowQuery = new FlowQuery([$taxonomy]);
//        $vocabulary = $flowQuery
//            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
//            ->get(0);
//
//        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary->getContextPath()]);
//    }
//
//    /**
//     * Delete the given taxonomy
//     *
//     * @param NodeInterface $taxonomy
//     * @return void
//     */
//    public function deleteTaxonomyAction(NodeInterface $taxonomy)
//    {
//        if ($taxonomy->isAutoCreated()) {
//            throw new \Exception('cannot delete autocrated taxonomies');
//        }
//
//        $flowQuery = new FlowQuery([$taxonomy]);
//        $vocabulary = $flowQuery
//            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
//            ->get(0);
//
//        $taxonomy->remove();
//
//        $this->addFlashMessage(
//            sprintf('Deleted taxonomy %s', $taxonomy->getPath())
//        );
//
//        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary]);
//    }

    protected function findNodeByAddress(NodeAddress $nodeAddress): ?Node
    {
        $contentRepository = $this->taxonomyService->getContentRepository();
        $contentGraph = $contentRepository->getContentGraph();
        $subgraph = $contentGraph->getSubgraph(
            $nodeAddress->contentStreamId,
            $nodeAddress->dimensionSpacePoint,
            VisibilityConstraints::withoutRestrictions()
        );
        return $subgraph->findNodeById($nodeAddress->nodeAggregateId);
    }

    protected function findSubgraph(): ContentSubgraphInterface
    {
        $contentRepository = $this->taxonomyService->getContentRepository();
        $liveWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(\Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName::forLive());
        $generalizations = $contentRepository->getVariationGraph()->getRootGeneralizations();
        $contentGraph = $contentRepository->getContentGraph();
        $subgraph = $contentGraph->getSubgraph(
            $liveWorkspace->currentContentStreamId,
            reset($generalizations),
            VisibilityConstraints::withoutRestrictions()
        );
        return $subgraph;
    }
}
