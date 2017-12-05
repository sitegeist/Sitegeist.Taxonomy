<?php
namespace Sitegeist\Taxonomy\Eel;

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

    /**
     * @param ContentContext
     * @return NodeInterface
     */
    public function getRootNode(ContentContext $context = null)
    {
        return $this->taxonomyService->getRootNode($context);
    }

    /**
     * @param NodeInterface[]|NodeInterface $value
     */
    public function extractTaxonomies($value)
    {
        return [];
    }

    /**
     * @param NodeInterface[]|NodeInterface $value
     */
    public function extractTaxonomiesAndParents($value)
    {
        return [];
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
