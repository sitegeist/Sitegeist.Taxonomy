<?php
namespace Sitegeist\Taxonomy\Eel\FlowQuery;

use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\Nodes;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\OperationInterface;
use Neos\Flow\Annotations as Flow;
use Sitegeist\Taxonomy\Service\TaxonomyService;

final class TaxonomyDescendantsOperation implements OperationInterface
{
    use FlattenSubtreeTrait;

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
        $contextNodes = $flowQuery->getContext();
        $nodes = Nodes::createEmpty();
        foreach ($contextNodes as $contextNode) {
            $subtree = $taxonomyService->findSubtree($contextNode);
            if ($subtree) {
                $nodes = $nodes->merge($this->flattenSubtree($subtree));
            }
        }
        $flowQuery->setContext(iterator_to_array($nodes->getIterator()));
    }

    public static function getShortName(): string
    {
        return 'taxonomyDescendants';
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
