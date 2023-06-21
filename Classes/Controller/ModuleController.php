<?php

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

declare(strict_types=1);

namespace Sitegeist\Taxonomy\Controller;

use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\DimensionSpace\OriginDimensionSpacePoint;
use Neos\ContentRepository\Core\Feature\NodeCreation\Command\CreateNodeAggregateWithNode;
use Neos\ContentRepository\Core\Feature\NodeModification\Command\SetNodeProperties;
use Neos\ContentRepository\Core\Feature\NodeModification\Dto\PropertyValuesToWrite;
use Neos\ContentRepository\Core\Feature\NodeRemoval\Command\RemoveNodeAggregate;
use Neos\ContentRepository\Core\Feature\NodeRenaming\Command\ChangeNodeAggregateName;
use Neos\ContentRepository\Core\Feature\NodeVariation\Command\CreateNodeVariant;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeName;
use Neos\ContentRepository\Core\SharedModel\Node\NodeVariantSelectionStrategy;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Annotations as Flow;
use Neos\Fusion\View\FusionView;
use Neos\Neos\FrontendRouting\NodeAddressFactory;
use Neos\Neos\Fusion\Helper\DimensionHelper;
use Neos\Neos\Fusion\Helper\NodeHelper;
use Sitegeist\Taxonomy\Service\TaxonomyService;
use Neos\Flow\Persistence\PersistenceManagerInterface;
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
     * @var FusionView
     */
    protected $view;


    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="backendModule.additionalFusionIncludePathes")
     */
    protected $additionalFusionIncludePathes;

    /**
     * @var TaxonomyService
     * @Flow\Inject
     */
    protected $taxonomyService;

    /**
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * @var NodeAddressFactory
     */
    protected $nodeAddressFactory;

    /**
     * @Flow\Inject
     * @var DimensionHelper
     */
    protected $dimensionHelper;

    /**
     * @Flow\Inject
     * @var NodeHelper
     */
    protected $nodeHelper;

    public function initializeObject()
    {
        $this->contentRepository = $this->taxonomyService->getContentRepository();
        $this->nodeAddressFactory = NodeAddressFactory::create($this->contentRepository);
    }

    /**
     * Initialize the view
     *
     * @param  ViewInterface $view
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
        $fusionPathes = ['resource://Sitegeist.Taxonomy/Private/Fusion/Backend'];
        if ($this->additionalFusionIncludePathes && is_array($this->additionalFusionIncludePathes)) {
            $fusionPathes = Arrays::arrayMergeRecursiveOverrule($fusionPathes, $this->additionalFusionIncludePathes);
        }
        $this->view->setFusionPathPatterns($fusionPathes);
    }

    /**
     * Show an overview of available vocabularies
     */
    public function indexAction(string $rootNodeAddress = null): void
    {
        if (is_null($rootNodeAddress)) {
            $subgraph = $this->taxonomyService->getDefaultSubgraph();
            $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);
        } else {
            $rootNode = $this->taxonomyService->getNodeByNodeAddress($rootNodeAddress);
            $subgraph = $this->taxonomyService->getSubgraphForNode($rootNode);
        }

        $vocabularies = $this->taxonomyService->findAllVocabularies($subgraph);

        $this->view->assign('rootNode', $rootNode);
        $this->view->assign('rootNodeAddress', $rootNode ? $this->nodeAddressFactory->createFromNode($rootNode)->serializeForUri() : null);
        $this->view->assign('vocabularies', $vocabularies);
    }

    /**
     * Switch to a modified content context and redirect to the given action
     *
     * @param string $targetAction the target action to redirect to
     * @param string $targetProperty the property in the target action that will accept the node
     * @param string $contextNodeAddress the node to adjust the context for
     * @param array $dimensions array with dimensionName, presetName combinations
     * @return void
     */
    public function changeDimensionAction(string $targetAction, string $targetProperty, string $contextNodeAddress, array $dimensions = [])
    {
        $contextNode = $this->taxonomyService->getNodeByNodeAddress($contextNodeAddress);
        foreach ($dimensions as $dimensionName => $dimensionValue) {
            $contextNodeInDimension = $this->dimensionHelper->findVariantInDimension($contextNode, $dimensionName, $dimensionValue);
            if ($contextNodeInDimension instanceof Node) {
                $contextNode = $contextNodeInDimension;
            }
        }
        $this->redirect($targetAction, null, null, [$targetProperty => $this->nodeHelper->serializedNodeAddress($contextNode)]);
    }

    /**
     * Show the given vocabulary
     */
    public function vocabularyAction(string $vocabularyNodeAddress)
    {
        $vocabularyNode = $this->taxonomyService->getNodeByNodeAddress($vocabularyNodeAddress);
        $subgraph = $this->taxonomyService->getSubgraphForNode($vocabularyNode);
        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);
        $vocabularySubtree = $this->taxonomyService->findSubtree($vocabularyNode);

        $this->view->assign('rootNode', $rootNode);
        $this->view->assign('vocabularyNode', $vocabularyNode);
        $this->view->assign('vocabularySubtree', $vocabularySubtree);
    }

    /**
     * Display a form that allows to create a new vocabulary
     */
    public function newVocabularyAction(string $rootNodeAddress = null): void
    {
        $node = $this->taxonomyService->getNodeByNodeAddress($rootNodeAddress);
        $this->view->assign('rootNode', $node);
    }

    /**
     * Create a new vocabulary
     *
     * @param string $rootNodeAddress root node address
     * @param array $properties
     */
    public function createVocabularyAction(string $rootNodeAddress, string $name, array $properties)
    {
        $contentRepository = $this->taxonomyService->getContentRepository();

        $rootNode = $this->taxonomyService->getNodeByNodeAddress($rootNodeAddress);
        $subgraph = $this->taxonomyService->getSubgraphForNode($rootNode);
        $liveWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(WorkspaceName::forLive());
        $generalizations = $contentRepository->getVariationGraph()->getRootGeneralizations();
        $nodeAddress = $this->nodeAddressFactory->createFromUriString($rootNodeAddress);
        $originDimensionSpacePoint = OriginDimensionSpacePoint::fromDimensionSpacePoint($nodeAddress->dimensionSpacePoint);

        // create node
        $nodeAggregateId = NodeAggregateId::create();
        $nodeTypeName = $this->taxonomyService->getVocabularyNodeTypeName();
        $commandResult = $contentRepository->handle(
            new CreateNodeAggregateWithNode(
                $liveWorkspace->currentContentStreamId,
                $nodeAggregateId,
                $nodeTypeName,
                $originDimensionSpacePoint,
                $rootNode->nodeAggregateId,
                null,
                NodeName::transliterateFromString($name),
                PropertyValuesToWrite::fromArray($properties)
            )
        );
        $commandResult->block();

        // create required generalizations
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
                    $originDimensionSpacePoint2
                )
            );
            $commandResult->block();
        }

        $newVocabularyNode = $subgraph->findNodeById($nodeAggregateId);

        $this->addFlashMessage(
            sprintf('Created vocabulary %s', $newVocabularyNode->getLabel()),
            'Create Vocabulary'
        );

        $this->redirect('index');
    }

    /**
     * Show a form that allows to modify the given vocabulary
     */
    public function editVocabularyAction(string $vocabularyNodeAddress)
    {
        $contentRepository = $this->taxonomyService->getContentRepository();
        $vocabularyNode = $this->taxonomyService->getNodeByNodeAddress($vocabularyNodeAddress);

        $subgraph = $contentRepository->getContentGraph()->getSubgraph(
            $vocabularyNode->subgraphIdentity->contentStreamId,
            $vocabularyNode->subgraphIdentity->dimensionSpacePoint,
            $vocabularyNode->subgraphIdentity->visibilityConstraints,
        );

        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);

        $this->view->assign('rootNode', $rootNode);
        $this->view->assign('vocabularyNode', $vocabularyNode);
    }

    /**
     * Apply changes to the given vocabulary
     */
    public function updateVocabularyAction(string $vocabularyNodeAddress, string $name, array $properties)
    {
        $vocabularyNode = $this->taxonomyService->getNodeByNodeAddress($vocabularyNodeAddress);
        $subgraph = $this->taxonomyService->getSubgraphForNode($vocabularyNode);
        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);

        $commandResult = $this->contentRepository->handle(
            new SetNodeProperties(
                $vocabularyNode->subgraphIdentity->contentStreamId,
                $vocabularyNode->nodeAggregateId,
                $vocabularyNode->originDimensionSpacePoint,
                PropertyValuesToWrite::fromArray($properties)
            )
        );
        $commandResult->block();
        if ($name != $vocabularyNode->nodeName->value) {
            $commandResult = $this->contentRepository->handle(
                new ChangeNodeAggregateName(
                    $vocabularyNode->subgraphIdentity->contentStreamId,
                    $vocabularyNode->nodeAggregateId,
                    NodeName::transliterateFromString($name)
                )
            );
            $commandResult->block();
        }
        $updatedVocabularyNode = $subgraph->findNodeById($vocabularyNode->nodeAggregateId);

        $this->addFlashMessage(
            sprintf('Updated vocabulary %s', $updatedVocabularyNode->getLabel())
        );
        $this->redirect('index', null, null, ['rootNodeAddress' => $this->nodeAddressFactory->createFromNode($rootNode)]);
    }

    /**
     * Delete the given vocabulary
     */
    public function deleteVocabularyAction(string $vocabularyNodeAddress)
    {
        $vocabularyNode = $this->taxonomyService->getNodeByNodeAddress($vocabularyNodeAddress);
        $subgraph = $this->taxonomyService->getSubgraphForNode($vocabularyNode);
        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);
        $liveWorkspace = $this->contentRepository->getWorkspaceFinder()->findOneByName(\Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName::forLive());

        $commandResult = $this->contentRepository->handle(
            new RemoveNodeAggregate(
                $liveWorkspace->currentContentStreamId,
                $vocabularyNode->nodeAggregateId,
                $vocabularyNode->originDimensionSpacePoint->toDimensionSpacePoint(),
                NodeVariantSelectionStrategy::STRATEGY_ALL_VARIANTS
            )
        );
        $commandResult->block();

        $this->addFlashMessage(
            sprintf('Deleted vocabulary %s', $vocabularyNode->getLabel())
        );

        $this->redirect('index', null, null, ['rootNodeAddress' => $this->nodeAddressFactory->createFromNode($rootNode)]);
    }

    /**
     * Show a form to create a new taxonomy
     */
    public function newTaxonomyAction(string $parentNodeAddress)
    {
        $parentNode = $this->taxonomyService->getNodeByNodeAddress($parentNodeAddress);
        $subgraph = $this->taxonomyService->getSubgraphForNode($parentNode);
        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);
        $vocabularyNode = null;

        if ($parentNode->nodeType->isOfType($this->taxonomyService->getTaxonomyNodeTypeName()->value)) {
            $vocabularyNode = $this->taxonomyService->findVocabularyForNode($parentNode);
        } elseif ($parentNode->nodeType->isOfType($this->taxonomyService->getVocabularyNodeTypeName()->value)) {
            $vocabularyNode = $parentNode;
        }

        $this->view->assign('rootNode', $rootNode);
        $this->view->assign('vocabularyNode', $vocabularyNode);
        $this->view->assign('parentNode', $parentNode);
    }

    /**
     * Create a new taxonomy
     */
    public function createTaxonomyAction(string $parentNodeAddress, string $name, array $properties): void
    {
        $parentNode = $this->taxonomyService->getNodeByNodeAddress($parentNodeAddress);
        $vocabularyNode = $this->taxonomyService->findVocabularyForNode($parentNode);
        $subgraph = $this->taxonomyService->getSubgraphForNode($parentNode);
        $liveWorkspace = $this->contentRepository->getWorkspaceFinder()->findOneByName(WorkspaceName::forLive());
        $generalizations = $this->contentRepository->getVariationGraph()->getRootGeneralizations();
        $nodeAddress = $this->nodeAddressFactory->createFromUriString($parentNodeAddress);
        $originDimensionSpacePoint = OriginDimensionSpacePoint::fromDimensionSpacePoint($nodeAddress->dimensionSpacePoint);

        // create node
        $nodeAggregateId = NodeAggregateId::create();
        $nodeTypeName = $this->taxonomyService->getTaxonomyNodeTypeName();
        $commandResult = $this->contentRepository->handle(
            new CreateNodeAggregateWithNode(
                $liveWorkspace->currentContentStreamId,
                $nodeAggregateId,
                $nodeTypeName,
                $originDimensionSpacePoint,
                $parentNode->nodeAggregateId,
                null,
                NodeName::transliterateFromString($name),
                PropertyValuesToWrite::fromArray($properties)
            )
        );
        $commandResult->block();

        // create required generalizations
        foreach ($generalizations as $dimensionSpacePoint) {
            $originDimensionSpacePoint2 = OriginDimensionSpacePoint::fromDimensionSpacePoint($dimensionSpacePoint);
            if ($originDimensionSpacePoint->equals($originDimensionSpacePoint2)) {
                continue;
            }

            $commandResult = $this->contentRepository->handle(
                new CreateNodeVariant(
                    $liveWorkspace->currentContentStreamId,
                    $nodeAggregateId,
                    $originDimensionSpacePoint,
                    $originDimensionSpacePoint2
                )
            );
            $commandResult->block();
        }

        $newTaxonomyNode = $subgraph->findNodeById($nodeAggregateId);

        $this->addFlashMessage(
            sprintf('Created taxonomy %s', $newTaxonomyNode->getLabel()),
            'Create taxomony'
        );

        $this->redirect(
            'vocabulary',
            null,
            null,
            ['vocabularyNodeAddress' => $this->nodeAddressFactory->createFromNode($vocabularyNode)]
        );
    }

    /**
     * Display a form that allows to modify the given taxonomy
     */
    public function editTaxonomyAction(string $taxonomyNodeAddress)
    {
        $taxonomyNode = $this->taxonomyService->getNodeByNodeAddress($taxonomyNodeAddress);
        $vocabularyNode = $this->taxonomyService->findVocabularyForNode($taxonomyNode);

        $this->view->assign('vocabularyNode', $vocabularyNode);
        $this->view->assign('taxonomyNode', $taxonomyNode);
    }

    /**
     * Apply changes to the given taxonomy
     */
    public function updateTaxonomyAction(string $taxonomyNodeAddress, string $name, array $properties)
    {
        $taxonomyNode = $this->taxonomyService->getNodeByNodeAddress($taxonomyNodeAddress);
        $vocabularyNode = $this->taxonomyService->findVocabularyForNode($taxonomyNode);
        $subgraph = $this->taxonomyService->getSubgraphForNode($taxonomyNode);

        $commandResult = $this->contentRepository->handle(
            new SetNodeProperties(
                $taxonomyNode->subgraphIdentity->contentStreamId,
                $taxonomyNode->nodeAggregateId,
                $taxonomyNode->originDimensionSpacePoint,
                PropertyValuesToWrite::fromArray($properties)
            )
        );
        $commandResult->block();
        if ($name != $taxonomyNode->nodeName->value) {
            $commandResult = $this->contentRepository->handle(
                new ChangeNodeAggregateName(
                    $taxonomyNode->subgraphIdentity->contentStreamId,
                    $taxonomyNode->nodeAggregateId,
                    NodeName::transliterateFromString($name)
                )
            );
            $commandResult->block();
        }

        $updatedTaxonomyNode = $subgraph->findNodeById($vocabularyNode->nodeAggregateId);

        $this->addFlashMessage(
            sprintf('Updated taxonomy %s', $updatedTaxonomyNode->getLabel())
        );

        $this->redirect('vocabulary', null, null, ['vocabularyNodeAddress' => $this->nodeAddressFactory->createFromNode($vocabularyNode)]);
    }

    /**
     * Delete the given taxonomy
     */
    public function deleteTaxonomyAction(string $taxonomyNodeAddress)
    {
        $taxonomyNode = $this->taxonomyService->getNodeByNodeAddress($taxonomyNodeAddress);
        $vocabularyNode = $this->taxonomyService->findVocabularyForNode($taxonomyNode);
        $liveWorkspace = $this->contentRepository->getWorkspaceFinder()->findOneByName(WorkspaceName::forLive());

        $commandResult = $this->contentRepository->handle(
            new RemoveNodeAggregate(
                $liveWorkspace->currentContentStreamId,
                $taxonomyNode->nodeAggregateId,
                $taxonomyNode->originDimensionSpacePoint->toDimensionSpacePoint(),
                NodeVariantSelectionStrategy::STRATEGY_ALL_VARIANTS
            )
        );
        $commandResult->block();

        $this->addFlashMessage(
            sprintf('Deleted taxonomy %s', $taxonomyNode->getLabel())
        );

        $this->redirect('vocabulary', null, null, ['vocabularyNodeAddress' => $this->nodeAddressFactory->createFromNode($vocabularyNode)]);
    }
}
