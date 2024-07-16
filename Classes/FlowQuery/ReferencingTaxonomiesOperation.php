<?php

declare(strict_types=1);

namespace Sitegeist\Taxonomy\FlowQuery;

use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindBackReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\NodeType\NodeTypeCriteria;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;
use Neos\Flow\Annotations as Flow;

class ReferencingTaxonomiesOperation extends AbstractOperation
{
    use CreateNodeHashTrait;
    use FlattenSubtreeTrait;

    /**
     * @var string
     */
    protected static $shortName = 'referencingTaxonomies';

    /**
     * @var integer
     */
    protected static $priority = 100;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    /**
     * {@inheritdoc}
     *
     * @param array<int,mixed> $context (or array-like object) onto which this operation should be applied
     * @return boolean true if the operation can be applied onto the $context, false otherwise
     */
    public function canEvaluate($context)
    {
        return isset($context[0]) && ($context[0] instanceof Node);
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery<int,mixed> $flowQuery the FlowQuery object
     * @param array<int,mixed> $arguments the arguments for this operation
     * @return void
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments)
    {
        $nodes = [];
        $findBackReferencesFilter = FindBackReferencesFilter::create(
            nodeTypes: NodeTypeCriteria::fromFilterString('Neos.Neos:Document'),
            referenceName: 'taxonomyReferences'
        );

        /**
         * @var Node $node
         */
        foreach ($flowQuery->getContext() as $node) {
            $subgraph = $this->contentRepositoryRegistry->subgraphForNode($node);
            $references = $subgraph->findBackReferences($node->nodeAggregateId, $findBackReferencesFilter);
            foreach ($references as $reference) {
                $nodes[] = $reference->node;
            }
        }

        $nodesByHash = [];
        foreach ($nodes as $node) {
            $hash = $this->createNodeHash($node);
            if (!array_key_exists($hash, $nodesByHash)) {
                $nodesByHash[$hash] = $node;
            }
        }
        $flowQuery->setContext(array_values($nodesByHash));

        $flowQuery->setContext($nodes);
    }
}
