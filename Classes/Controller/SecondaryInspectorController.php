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

use Neos\ContentRepository\Core\Projection\ContentGraph\AbsoluteNodePath;
use Neos\ContentRepository\Core\Projection\ContentGraph\Subtree;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\View\JsonView;
use Sitegeist\Taxonomy\Service\TaxonomyService;

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

    /**
     * @var array
     */
    protected $supportedMediaTypes = ['application/json'];

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    public function treeAction(string $contextNode, string $startingPoint): void
    {
        $contextNode = $this->taxonomyService->getNodeByNodeAddress($contextNode);
        $subgraph =  $this->taxonomyService->getSubgraphForNode($contextNode);

        $path = AbsoluteNodePath::fromString($startingPoint);
        $startingPoint = $subgraph->findNodeByAbsolutePath($path);

        $taxonomySubtree = $this->taxonomyService->findSubtree($startingPoint);

        $this->view->assign('value', $this->toJson($taxonomySubtree));
    }

    protected function toJson(Subtree $subtree, array $pathSoFar = []): array
    {
        $result = [];

        $result['identifier'] = $subtree->node->nodeAggregateId->value;
        $result['path'] = implode('/', $pathSoFar);
        $result['nodeType'] = $subtree->node->nodeType->name->value;
        $result['label'] = $subtree->node->getLabel();
        $result['title'] = $subtree->node->getProperty('title');
        $result['description'] = $subtree->node->getProperty('description');

        $result['children'] = [];

        $name = $subtree->node->nodeName ? $subtree->node->nodeName->value : $subtree->node->getProperty('title');

        foreach ($subtree->children as $childSubtree) {
            $result['children'][] = $this->toJson($childSubtree, [...$pathSoFar, $name]);
        }

        return $result;
    }
}
