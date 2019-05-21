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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\View\JsonView;

use Neos\ContentRepository\Domain\Model\NodeInterface;

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

    /**
     * @param NodeInterface $contextNode
     * @return void
     */
    public function treeAction(NodeInterface $contextNode): void
    {
        $taxonomyTreeAsArray = $this->taxonomyService
            ->getTaxonomyTreeAsArray($contextNode);

        $this->view->assign('value', $taxonomyTreeAsArray);
    }
}
