<?php
namespace Sitegeist\Taxonomy\Eel;

use Neos\ContentGraph\DoctrineDbalAdapter\Domain\Repository\ContentSubgraph;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Sitegeist\Taxonomy\Service\TaxonomyService;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Eel\ProtectedContextAwareInterface;

class TaxonomyHelper implements ProtectedContextAwareInterface
{

    /**
     * @var TaxonomyService
     * @Flow\Inject
     */
    protected $taxonomyService;

    public function root(ContentSubgraph $subgraph = null): Node
    {
        return $this->taxonomyService->findOrCreateRoot($subgraph);
    }

    public function vocabulary(ContentSubgraph $subgraph, string $vocabulary): ?Node
    {
        return $this->taxonomyService->findVocabularyByName($subgraph, $vocabulary);
    }

    public function taxonomy(ContentSubgraph $subgraph, array $path = []): ?Node
    {
        return $this->taxonomyService->findVocabularyOrTaxonomyByPath($subgraph, $path);
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
