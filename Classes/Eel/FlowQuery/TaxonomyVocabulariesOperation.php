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

final class TaxonomyVocabulariesOperation implements OperationInterface
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
        $contextNodes = $flowQuery->getContext();
        $vocabularyNodesArray = [];
        foreach ($contextNodes as $contextNode) {
            $vocabularyNodesArray[] = $taxonomyService->findVocabularyForNode($contextNode);
        }
        $flowQuery->setContext($vocabularyNodesArray);
    }

    public static function getShortName(): string
    {
        return 'taxonomyVocabularies';
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
