<?php
namespace Sitegeist\Taxonomy\Eel\FlowQuery;

use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindBackReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\Nodes;
use Neos\ContentRepository\Core\Projection\ContentGraph\NodeTypeConstraints;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\OperationInterface;
use Neos\Flow\Annotations as Flow;
use Sitegeist\Taxonomy\Service\TaxonomyService;

final class TaxonomyReferencingNodesOperation implements OperationInterface
{
    /**
     * @Flow\InjectConfiguration(path="contentRepository.taxonomyNodeType")
     */
    protected string $taxonomyNodeType;

    public function canEvaluate($context): bool
    {
        return count($context) === 0 || (isset($context[0]) && ($context[0] instanceof Node) && $context[0]->nodeType->isOfType($this->taxonomyNodeType));
    }

    public function evaluate(FlowQuery $flowQuery, array $arguments): void
    {
        $taxonomyService = new TaxonomyService();
        $filter = FindBackReferencesFilter::create();
        if (isset($arguments[0])) {
            $filter = $filter->with(nodeTypeConstraints: NodeTypeConstraints::fromFilterString($arguments[0]));
        }
        if (isset($arguments[1])) {
            $filter = $filter->with(referenceName: $arguments[0]);
        }
        $referencingNodesArray = [];
        /** @var Node $contextNode */
        foreach ($flowQuery->getContext() as $contextNode) {
            $subgraph = $taxonomyService->getSubgraphForNode($contextNode);
            $backReferences = $subgraph->findBackReferences($contextNode->nodeAggregateId, $filter);
            foreach ($backReferences as $backReference) {
                $referencingNodesArray[] = $backReference->node;
            }
        }
        $flowQuery->setContext($referencingNodesArray);
    }

    public static function getShortName(): string
    {
        return 'taxonomyReferencingNodes';
    }

    public static function getPriority(): int
    {
        return 100;
    }

    public static function isFinal(): bool
    {
        return false;
    }
}
