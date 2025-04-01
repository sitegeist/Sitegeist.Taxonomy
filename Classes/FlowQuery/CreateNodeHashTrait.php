<?php

declare(strict_types=1);

namespace Sitegeist\Taxonomy\FlowQuery;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;

trait CreateNodeHashTrait
{
    /**
     * Create a string hash containing the nodeAggregateId, cr-id, workspaceName, dimensionSpacePoint->hash
     * and visibilityConstraints->hash. To be used for ensuring uniqueness or removing nodes.
     *
     * @see Node::equals() for comparison
     */
    protected function createNodeHash(Node $node): string
    {
        return md5(
            implode(
                ':',
                [
                    $node->aggregateId->value,
                    $node->contentRepositoryId->value,
                    $node->workspaceName->value,
                    $node->dimensionSpacePoint->hash,
                    $node->visibilityConstraints->getHash()
                ]
            )
        );
    }
}
