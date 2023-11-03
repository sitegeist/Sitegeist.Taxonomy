<?php

declare(strict_types=1);

namespace Sitegeist\Taxonomy\FlowQuery;

use Neos\ContentRepository\Core\NodeType\NodeTypeNames;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\NodeType\NodeTypeCriteria;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;
use Neos\Flow\Annotations as Flow;
use Sitegeist\Taxonomy\Service\TaxonomyService;

class ReferencedTaxonomiesOperation extends AbstractOperation
{
    use CreateNodeHashTrait;
    use FlattenSubtreeTrait;

    /**
     * @var string
     */
    protected static $shortName = 'referencedTaxonomies';

    /**
     * @var integer
     */
    protected static $priority = 100;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    #[Flow\Inject]
    protected TaxonomyService $taxonomyService;

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
        $findReferencesFilter = FindReferencesFilter::create(
            nodeTypes: NodeTypeCriteria::create(
                NodeTypeNames::fromArray([$this->taxonomyService->getTaxonomyNodeTypeName()]),
                NodeTypeNames::createEmpty()
            ),
            referenceName: 'taxonomyReferences'
        );

        /**
         * @var Node $node
         */
        foreach ($flowQuery->getContext() as $node) {
            $subgraph = $this->contentRepositoryRegistry->subgraphForNode($node);
            $references = $subgraph->findReferences($node->nodeAggregateId, $findReferencesFilter);
            foreach ($references as $reference) {
                $nodes[] = $reference->node;
            }
        }
        $flowQuery->setContext($nodes);
    }
}
