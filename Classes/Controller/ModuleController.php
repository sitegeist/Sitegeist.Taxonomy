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
use Neos\Neos\Domain\Exception\LiveWorkspaceIsMissing;
use Neos\Neos\FrontendRouting\NodeAddressFactory;
use Neos\Neos\Fusion\Helper\DimensionHelper;
use Neos\Neos\Fusion\Helper\NodeHelper;
use Sitegeist\Taxonomy\Service\TaxonomyService;
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

    #[Flow\InjectConfiguration(path: "backendModule.additionalFusionIncludePathes")]
    protected mixed $additionalFusionIncludePathes;

    #[Flow\Inject(lazy: false)]
    protected TaxonomyService $taxonomyService;

    #[Flow\Inject(lazy: false)]
    protected DimensionHelper $dimensionHelper;

    #[Flow\Inject(lazy: false)]
    protected NodeHelper $nodeHelper;

    protected ContentRepository $contentRepository;

    protected NodeAddressFactory $nodeAddressFactory;

    public function initializeObject(): void
    {
        $this->contentRepository = $this->taxonomyService->getContentRepository();
        $this->nodeAddressFactory = NodeAddressFactory::create($this->contentRepository);
    }

    public function initializeView(ViewInterface $view): void
    {
        $fusionPathes = ['resource://Sitegeist.Taxonomy/Private/Fusion/Backend'];
        if (is_array($this->additionalFusionIncludePathes) && !empty($this->additionalFusionIncludePathes)) {
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
        $this->view->assign('rootNodeAddress', $this->nodeAddressFactory->createFromNode($rootNode)->serializeForUri());
        $this->view->assign('vocabularies', $vocabularies);
    }

    /**
     * Switch to a modified content context and redirect to the given action
     *
     * @phpstan-param  array<string,string> $dimensions
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
    public function vocabularyAction(string $vocabularyNodeAddress): void
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
    public function newVocabularyAction(string $rootNodeAddress): void
    {
        $node = $this->taxonomyService->getNodeByNodeAddress($rootNodeAddress);
        $this->view->assign('rootNode', $node);
    }

    /**
     * Create a new vocabulary
     *
     * @param array<string, string> $properties
     */
    public function createVocabularyAction(string $rootNodeAddress, string $name, array $properties): void
    {
        $contentRepository = $this->taxonomyService->getContentRepository();

        $rootNode = $this->taxonomyService->getNodeByNodeAddress($rootNodeAddress);
        $subgraph = $this->taxonomyService->getSubgraphForNode($rootNode);
        $liveWorkspace = $this->taxonomyService->getLiveWorkspace();
        $generalizations = $contentRepository->getVariationGraph()->getRootGeneralizations();
        $nodeAddress = $this->nodeAddressFactory->createFromUriString($rootNodeAddress);
        $originDimensionSpacePoint = OriginDimensionSpacePoint::fromDimensionSpacePoint($nodeAddress->dimensionSpacePoint);

        // create node
        $nodeAggregateId = NodeAggregateId::create();
        $nodeTypeName = $this->taxonomyService->getVocabularyNodeTypeName();
        $commandResult = $contentRepository->handle(
            CreateNodeAggregateWithNode::create(
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

            $contentRepository->handle(
                CreateNodeVariant::create(
                    $liveWorkspace->currentContentStreamId,
                    $nodeAggregateId,
                    $originDimensionSpacePoint,
                    $originDimensionSpacePoint2
                )
            );
        }

        $newVocabularyNode = $subgraph->findNodeById($nodeAggregateId);

        if ($newVocabularyNode) {
            $this->addFlashMessage(
                sprintf('Created vocabulary %s', $newVocabularyNode->getLabel()),
                'Create Vocabulary'
            );
        }

        $this->redirect('index');
    }

    /**
     * Show a form that allows to modify the given vocabulary
     */
    public function editVocabularyAction(string $vocabularyNodeAddress): void
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
     *
     * @param array<string, string> $properties
     */
    public function updateVocabularyAction(string $vocabularyNodeAddress, string $name, array $properties): void
    {
        $vocabularyNode = $this->taxonomyService->getNodeByNodeAddress($vocabularyNodeAddress);
        $subgraph = $this->taxonomyService->getSubgraphForNode($vocabularyNode);
        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);

        $commandResult = $this->contentRepository->handle(
            SetNodeProperties::create(
                $vocabularyNode->subgraphIdentity->contentStreamId,
                $vocabularyNode->nodeAggregateId,
                $vocabularyNode->originDimensionSpacePoint,
                PropertyValuesToWrite::fromArray($properties)
            )
        );

        if ($name != $vocabularyNode->nodeName?->value) {
            $commandResult = $this->contentRepository->handle(
                ChangeNodeAggregateName::create(
                    $vocabularyNode->subgraphIdentity->contentStreamId,
                    $vocabularyNode->nodeAggregateId,
                    NodeName::transliterateFromString($name)
                )
            );
        }

        $commandResult->block();

        $updatedVocabularyNode = $subgraph->findNodeById($vocabularyNode->nodeAggregateId);

        if ($updatedVocabularyNode) {
            $this->addFlashMessage(
                sprintf('Updated vocabulary %s', $updatedVocabularyNode->getLabel())
            );
        }

        $this->redirect('index', null, null, ['rootNodeAddress' => $this->nodeAddressFactory->createFromNode($rootNode)]);
    }

    /**
     * Delete the given vocabulary
     */
    public function deleteVocabularyAction(string $vocabularyNodeAddress): void
    {
        $vocabularyNode = $this->taxonomyService->getNodeByNodeAddress($vocabularyNodeAddress);
        $subgraph = $this->taxonomyService->getSubgraphForNode($vocabularyNode);
        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);
        $liveWorkspace = $this->taxonomyService->getLiveWorkspace();

        $commandResult = $this->contentRepository->handle(
            RemoveNodeAggregate::create(
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
    public function newTaxonomyAction(string $parentNodeAddress): void
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
     * @param array<string, string> $properties
     */
    public function createTaxonomyAction(string $parentNodeAddress, string $name, array $properties): void
    {
        $parentNode = $this->taxonomyService->getNodeByNodeAddress($parentNodeAddress);
        $vocabularyNode = $this->taxonomyService->findVocabularyForNode($parentNode);
        $subgraph = $this->taxonomyService->getSubgraphForNode($parentNode);
        $liveWorkspace = $this->taxonomyService->getLiveWorkspace();

        $generalizations = $this->contentRepository->getVariationGraph()->getRootGeneralizations();
        $nodeAddress = $this->nodeAddressFactory->createFromUriString($parentNodeAddress);
        $originDimensionSpacePoint = OriginDimensionSpacePoint::fromDimensionSpacePoint($nodeAddress->dimensionSpacePoint);

        // create node
        $nodeAggregateId = NodeAggregateId::create();
        $nodeTypeName = $this->taxonomyService->getTaxonomyNodeTypeName();
        $commandResult = $this->contentRepository->handle(
            CreateNodeAggregateWithNode::create(
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
                CreateNodeVariant::create(
                    $liveWorkspace->currentContentStreamId,
                    $nodeAggregateId,
                    $originDimensionSpacePoint,
                    $originDimensionSpacePoint2
                )
            );
        }

        $newTaxonomyNode = $subgraph->findNodeById($nodeAggregateId);

        if ($newTaxonomyNode) {
            $this->addFlashMessage(
                sprintf('Created taxonomy %s', $newTaxonomyNode->getLabel()),
                'Create taxomony'
            );
        }

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
    public function editTaxonomyAction(string $taxonomyNodeAddress): void
    {
        $taxonomyNode = $this->taxonomyService->getNodeByNodeAddress($taxonomyNodeAddress);
        $vocabularyNode = $this->taxonomyService->findVocabularyForNode($taxonomyNode);

        $this->view->assign('vocabularyNode', $vocabularyNode);
        $this->view->assign('taxonomyNode', $taxonomyNode);
    }

    /**
     * Apply changes to the given taxonomy
     *
     * @param array<string, string> $properties
     */
    public function updateTaxonomyAction(string $taxonomyNodeAddress, string $name, array $properties): void
    {
        $taxonomyNode = $this->taxonomyService->getNodeByNodeAddress($taxonomyNodeAddress);
        $vocabularyNode = $this->taxonomyService->findVocabularyForNode($taxonomyNode);
        $subgraph = $this->taxonomyService->getSubgraphForNode($taxonomyNode);

        $commandResult = $this->contentRepository->handle(
            SetNodeProperties::create(
                $taxonomyNode->subgraphIdentity->contentStreamId,
                $taxonomyNode->nodeAggregateId,
                $taxonomyNode->originDimensionSpacePoint,
                PropertyValuesToWrite::fromArray($properties)
            )
        );
        if ($name != $taxonomyNode->nodeName?->value) {
            $commandResult = $this->contentRepository->handle(
                ChangeNodeAggregateName::create(
                    $taxonomyNode->subgraphIdentity->contentStreamId,
                    $taxonomyNode->nodeAggregateId,
                    NodeName::transliterateFromString($name)
                )
            );
        }
        $commandResult->block();

        $updatedTaxonomyNode = $subgraph->findNodeById($vocabularyNode->nodeAggregateId);

        if ($updatedTaxonomyNode) {
            $this->addFlashMessage(
                sprintf('Updated taxonomy %s', $updatedTaxonomyNode->getLabel())
            );
        }

        $this->redirect('vocabulary', null, null, ['vocabularyNodeAddress' => $this->nodeAddressFactory->createFromNode($vocabularyNode)]);
    }

    /**
     * Delete the given taxonomy
     */
    public function deleteTaxonomyAction(string $taxonomyNodeAddress): void
    {
        $taxonomyNode = $this->taxonomyService->getNodeByNodeAddress($taxonomyNodeAddress);
        $vocabularyNode = $this->taxonomyService->findVocabularyForNode($taxonomyNode);
        $liveWorkspace = $this->taxonomyService->getLiveWorkspace();

        $commandResult = $this->contentRepository->handle(
            RemoveNodeAggregate::create(
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
