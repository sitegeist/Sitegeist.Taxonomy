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

use Neos\ContentRepository\Core\Projection\ContentGraph\AbsoluteNodePath;
use Neos\ContentRepository\Core\Projection\ContentGraph\Subtree;
use Neos\Neos\Domain\NodeLabel\NodeLabelGeneratorInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\View\JsonView;
use Sitegeist\Taxonomy\Service\TaxonomyService;
use Neos\Neos\Ui\ContentRepository\Service\NeosUiNodeService;
use Neos\Neos\FrontendRouting\SiteDetection\SiteDetectionResult;

/**
 * Class SecondaryInspectorController
 * @package Sitegeist\Monocle\Controller
 */
class SecondaryInspectorController extends ActionController
{
    /**
     * @var TaxonomyService
     * @Flow\Inject
     */
    protected $taxonomyService;

    #[Flow\Inject]
    protected NodeLabelGeneratorInterface $nodeLabelGenerator;

    #[Flow\Inject]
    protected NeosUiNodeService $nodeService;

    /**
     * @var string[]
     */
    protected $supportedMediaTypes = ['application/json'];

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    public function treeAction(string $contextNode, string $startingPoint): void
    {
        $contentRepositoryId = SiteDetectionResult::fromRequest($this->request->getHttpRequest())->contentRepositoryId;
        $node = $this->nodeService->findNodeBySerializedNodeAddress($contextNode, $contentRepositoryId);
        $subgraph =  $this->taxonomyService->getSubgraphForNode($node);

        $path = AbsoluteNodePath::fromString($startingPoint);
        $startNode = $subgraph->findNodeByAbsolutePath($path);
        if (!$startNode) {
            return;
        }
        $taxonomySubtree = $this->taxonomyService->findSubtree($startNode);
        if (!$taxonomySubtree) {
            return;
        }
        $this->view->assign('value', $this->toJson($taxonomySubtree));
    }

    /**
     * @return mixed[]
     */
    protected function toJson(Subtree $subtree, string $pathSoFar = null): array
    {
        $label = $this->nodeLabelGenerator->getLabel($subtree->node);
        $pathSegment = $subtree->node->name?->value ?? $label;
        $path = $pathSoFar ? $pathSoFar . ' - ' . $pathSegment : $pathSegment;
        $identifier = $subtree->node->aggregateId;
        $nodeType =  $subtree->node->nodeTypeName->value;
        $title = $subtree->node->getProperty('title');
        $description = $subtree->node->getProperty('description');
        $children = array_map(fn(Subtree $child)=>$this->toJson($child), $subtree->children);

        return [
            'identifier' => $identifier,
            'path' => $path,
            'nodeType' => $nodeType,
            'label' => $label,
            'title' => is_string($title) ? $title : $label,
            'description' => is_string($description) ? $description : '',
            'children' => $children
        ];
    }
}
