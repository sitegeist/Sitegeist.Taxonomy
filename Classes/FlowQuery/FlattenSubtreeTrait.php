<?php

declare(strict_types=1);

namespace Sitegeist\Taxonomy\FlowQuery;

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
