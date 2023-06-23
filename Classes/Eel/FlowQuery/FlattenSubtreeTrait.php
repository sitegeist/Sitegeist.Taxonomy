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

namespace Sitegeist\Taxonomy\Eel\FlowQuery;

use Neos\ContentRepository\Core\Projection\ContentGraph\Nodes;
use Neos\ContentRepository\Core\Projection\ContentGraph\Subtree;

trait FlattenSubtreeTrait
{
    protected function flattenSubtree(Subtree $subtree): Nodes
    {
        $nodes = Nodes::fromArray([$subtree->node]);
        foreach ($subtree->children as $child) {
            $nodes = $nodes->merge($this->flattenSubtree($child));
        }
        return $nodes;
    }
}
