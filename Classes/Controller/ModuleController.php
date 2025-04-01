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
use Neos\ContentRepository\Core\Feature\WorkspaceRebase\Command\RebaseWorkspace;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAddress;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeName;
use Neos\ContentRepository\Core\SharedModel\Node\NodeVariantSelectionStrategy;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Domain\NodeLabel\NodeLabelGeneratorInterface;
use Neos\Neos\Domain\Service\WorkspaceService;
use Neos\Neos\Fusion\Helper\DimensionHelper;
use Neos\Neos\Fusion\Helper\NodeHelper;
use Neos\Neos\Service\UserService;
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

    #[Flow\Inject(lazy: false)]
    protected SecurityContext $securityContext;

    #[Flow\Inject]
    protected NodeLabelGeneratorInterface $nodeLabelGenerator;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    #[Flow\Inject]
    protected UserService $userService;

    #[Flow\Inject]
    protected WorkspaceService $workspaceService;

    protected ContentRepository $contentRepository;

    public function initializeObject(): void
    {
        $this->contentRepository = $this->taxonomyService->getContentRepository();
    }

    public function initializeView(ViewInterface $view): void
    {
        $fusionPaths = ['resource://Sitegeist.Taxonomy/Private/Fusion/Backend'];
        if (is_array($this->additionalFusionIncludePathes) && !empty($this->additionalFusionIncludePathes)) {
            $fusionPaths = Arrays::arrayMergeRecursiveOverrule($fusionPaths, $this->additionalFusionIncludePathes);
        }
        $this->view->setFusionPathPatterns($fusionPaths);
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
        $this->view->assign('rootNodeAddress', NodeAddress::fromNode($rootNode)->toJson());
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
        $generalizations = $contentRepository->getVariationGraph()->getRootGeneralizations();
        $nodeAddress = NodeAddress::fromJsonString($rootNodeAddress);
        $originDimensionSpacePoint = OriginDimensionSpacePoint::fromDimensionSpacePoint($nodeAddress->dimensionSpacePoint);

        // create node
        $nodeAggregateId = NodeAggregateId::create();
        $nodeTypeName = $this->taxonomyService->getVocabularyNodeTypeName();
        $contentRepository->handle(
            CreateNodeAggregateWithNode::create(
                workspaceName: WorkspaceName::forLive(),
                nodeAggregateId: $nodeAggregateId,
                nodeTypeName: $nodeTypeName,
                originDimensionSpacePoint: $originDimensionSpacePoint,
                parentNodeAggregateId: $rootNode->aggregateId,
                initialPropertyValues: PropertyValuesToWrite::fromArray($properties)
            )->withNodeName(NodeName::transliterateFromString($name))
        );

        // create required generalizations
        foreach ($generalizations as $dimensionSpacePoint) {
            $originDimensionSpacePoint2 = OriginDimensionSpacePoint::fromDimensionSpacePoint($dimensionSpacePoint);
            if ($originDimensionSpacePoint->equals($originDimensionSpacePoint2)) {
                continue;
            }

            $contentRepository->handle(
                CreateNodeVariant::create(
                    WorkspaceName::forLive(),
                    $nodeAggregateId,
                    $originDimensionSpacePoint,
                    $originDimensionSpacePoint2
                )
            );
        }

        $this->rebaseCurrentUserWorkspace();

        $newVocabularyNode = $subgraph->findNodeById($nodeAggregateId);

        if ($newVocabularyNode) {
            $this->addFlashMessage(
                sprintf('Created vocabulary %s', $this->nodeLabelGenerator->getLabel($newVocabularyNode)),
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
        $vocabularyNode = $this->taxonomyService->getNodeByNodeAddress($vocabularyNodeAddress);
        $subgraph = $this->contentRepositoryRegistry->subgraphForNode($vocabularyNode);

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
        $subgraph = $this->contentRepositoryRegistry->subgraphForNode($vocabularyNode);
        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);

        $this->contentRepository->handle(
            SetNodeProperties::create(
                $vocabularyNode->workspaceName,
                $vocabularyNode->aggregateId,
                $vocabularyNode->originDimensionSpacePoint,
                PropertyValuesToWrite::fromArray($properties)
            )
        );

        if ($name != $vocabularyNode->name?->value) {
            $this->contentRepository->handle(
                ChangeNodeAggregateName::create(
                    $vocabularyNode->workspaceName,
                    $vocabularyNode->aggregateId,
                    NodeName::transliterateFromString($name)
                )
            );
        }

        $this->rebaseCurrentUserWorkspace();

        $updatedVocabularyNode = $subgraph->findNodeById($vocabularyNode->aggregateId);

        if ($updatedVocabularyNode) {
            $this->addFlashMessage(
                sprintf('Updated vocabulary %s', $this->nodeLabelGenerator->getLabel($updatedVocabularyNode))
            );
        }

        $this->redirect('index', null, null, ['rootNodeAddress' => NodeAddress::fromNode($rootNode)]);
    }

    /**
     * Delete the given vocabulary
     */
    public function deleteVocabularyAction(string $vocabularyNodeAddress): void
    {
        $vocabularyNode = $this->taxonomyService->getNodeByNodeAddress($vocabularyNodeAddress);
        $subgraph = $this->taxonomyService->getSubgraphForNode($vocabularyNode);
        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);

        $this->contentRepository->handle(
            RemoveNodeAggregate::create(
                WorkspaceName::forLive(),
                $vocabularyNode->aggregateId,
                $vocabularyNode->originDimensionSpacePoint->toDimensionSpacePoint(),
                NodeVariantSelectionStrategy::STRATEGY_ALL_VARIANTS
            )
        );
        $this->rebaseCurrentUserWorkspace();

        $this->addFlashMessage(
            sprintf('Deleted vocabulary %s', $this->nodeLabelGenerator->getLabel($vocabularyNode))
        );

        $this->redirect('index', null, null, ['rootNodeAddress' => NodeAddress::fromNode($rootNode)]);
    }

    /**
     * Show a form to create a new taxonomy
     */
    public function newTaxonomyAction(string $parentNodeAddress): void
    {
        $parentNode = $this->taxonomyService->getNodeByNodeAddress($parentNodeAddress);
        $subgraph = $this->taxonomyService->getSubgraphForNode($parentNode);
        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);
        $parentNodeType = $this->taxonomyService->getContentRepository()->getNodeTypeManager()
            ->getNodeType($parentNode->nodeTypeName);

        if ($parentNodeType?->isOfType($this->taxonomyService->getTaxonomyNodeTypeName())) {
            $vocabularyNode = $this->taxonomyService->findVocabularyForNode($parentNode);
        } elseif ($parentNodeType?->isOfType($this->taxonomyService->getVocabularyNodeTypeName())) {
            $vocabularyNode = $parentNode;
        } else {
            throw new \Exception('Don\'t know how to handle parent nodes of type ' . $parentNode->nodeTypeName->value);
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
        if ($parentNode->nodeTypeName->equals($this->taxonomyService->getVocabularyNodeTypeName())) {
            $vocabularyNode = $parentNode;
        } else {
            $vocabularyNode = $this->taxonomyService->findVocabularyForNode($parentNode);
        }
        $subgraph = $this->taxonomyService->getSubgraphForNode($parentNode);

        $generalizations = $this->contentRepository->getVariationGraph()->getRootGeneralizations();
        $nodeAddress = NodeAddress::fromJsonString($parentNodeAddress);
        $originDimensionSpacePoint = OriginDimensionSpacePoint::fromDimensionSpacePoint($nodeAddress->dimensionSpacePoint);

        // create node
        $nodeAggregateId = NodeAggregateId::create();
        $nodeTypeName = $this->taxonomyService->getTaxonomyNodeTypeName();
        $this->contentRepository->handle(
            CreateNodeAggregateWithNode::create(
                workspaceName: WorkspaceName::forLive(),
                nodeAggregateId: $nodeAggregateId,
                nodeTypeName: $nodeTypeName,
                originDimensionSpacePoint: $originDimensionSpacePoint,
                parentNodeAggregateId: $parentNode->aggregateId,
                initialPropertyValues: PropertyValuesToWrite::fromArray($properties)
            )->withNodeName(NodeName::transliterateFromString($name))
        );

        // create required generalizations
        foreach ($generalizations as $dimensionSpacePoint) {
            $originDimensionSpacePoint2 = OriginDimensionSpacePoint::fromDimensionSpacePoint($dimensionSpacePoint);
            if ($originDimensionSpacePoint->equals($originDimensionSpacePoint2)) {
                continue;
            }

            $this->contentRepository->handle(
                CreateNodeVariant::create(
                    WorkspaceName::forLive(),
                    $nodeAggregateId,
                    $originDimensionSpacePoint,
                    $originDimensionSpacePoint2
                )
            );
        }

        $this->rebaseCurrentUserWorkspace();
        $newTaxonomyNode = $subgraph->findNodeById($nodeAggregateId);

        if ($newTaxonomyNode) {
            $this->addFlashMessage(
                sprintf('Created taxonomy %s', $this->nodeLabelGenerator->getLabel($newTaxonomyNode)),
                'Create taxomony'
            );
        }

        $this->redirect(
            'vocabulary',
            null,
            null,
            ['vocabularyNodeAddress' => NodeAddress::fromNode($vocabularyNode)->toJson()]
        );
    }

    /**
     * Display a form that allows to modify the given taxonomy
     */
    public function editTaxonomyAction(string $taxonomyNodeAddress): void
    {
        $taxonomyNode = $this->taxonomyService->getNodeByNodeAddress($taxonomyNodeAddress);
        $vocabularyNode = $this->taxonomyService->findVocabularyForNode($taxonomyNode);
        $subgraph = $this->taxonomyService->getSubgraphForNode($vocabularyNode);
        $rootNode = $this->taxonomyService->findOrCreateRoot($subgraph);

        $this->view->assign('rootNode', $rootNode);
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

        $this->contentRepository->handle(
            SetNodeProperties::create(
                $taxonomyNode->workspaceName,
                $taxonomyNode->aggregateId,
                $taxonomyNode->originDimensionSpacePoint,
                PropertyValuesToWrite::fromArray($properties)
            )
        );
        if ($name != $taxonomyNode->name?->value) {
            $this->contentRepository->handle(
                ChangeNodeAggregateName::create(
                    $taxonomyNode->workspaceName,
                    $taxonomyNode->aggregateId,
                    NodeName::transliterateFromString($name)
                )
            );
        }
        $this->rebaseCurrentUserWorkspace();

        $updatedTaxonomyNode = $subgraph->findNodeById($vocabularyNode->aggregateId);

        if ($updatedTaxonomyNode) {
            $this->addFlashMessage(
                sprintf('Updated taxonomy %s', $this->nodeLabelGenerator->getLabel($updatedTaxonomyNode))
            );
        }

        $this->redirect('vocabulary', null, null, ['vocabularyNodeAddress' => NodeAddress::fromNode($vocabularyNode)->toJson()]);
    }

    /**
     * Delete the given taxonomy
     */
    public function deleteTaxonomyAction(string $taxonomyNodeAddress): void
    {
        $taxonomyNode = $this->taxonomyService->getNodeByNodeAddress($taxonomyNodeAddress);
        $vocabularyNode = $this->taxonomyService->findVocabularyForNode($taxonomyNode);

        $this->contentRepository->handle(
            RemoveNodeAggregate::create(
                WorkspaceName::forLive(),
                $taxonomyNode->aggregateId,
                $taxonomyNode->originDimensionSpacePoint->toDimensionSpacePoint(),
                NodeVariantSelectionStrategy::STRATEGY_ALL_VARIANTS
            )
        );
        $this->rebaseCurrentUserWorkspace();

        $this->addFlashMessage(
            sprintf('Deleted taxonomy %s', $this->nodeLabelGenerator->getLabel($taxonomyNode))
        );

        $this->redirect('vocabulary', null, null, ['vocabularyNodeAddress' => NodeAddress::fromNode($vocabularyNode)->toJson()]);
    }

    protected function rebaseCurrentUserWorkspace(): void
    {
        $user = $this->userService->getBackendUser();
        if ($user === null) {
            $this->redirectToUri($this->uriBuilder->uriFor('index', [], 'Login', 'Neos.Neos'));
        }

        $this->workspaceService->createPersonalWorkspaceForUserIfMissing($this->contentRepository->id, $user);
        $workspace = $this->workspaceService->getPersonalWorkspaceForUser($this->contentRepository->id, $user->getId());

        $this->contentRepository->handle(RebaseWorkspace::create($workspace->workspaceName));
    }
}
