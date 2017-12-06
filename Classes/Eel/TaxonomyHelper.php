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
     * @param ContentContext $context
     * @return NodeInterface
     */
    public function root(ContentContext $context = null)
    {
        return $this->taxonomyService->getRootNode($context);
    }

    /**
     * @param ContentContext $context
     * @param string $name Name of the vocabulary node
     * @return NodeInterface
     */
    public function vocabulary(ContentContext $context = null, $name)
    {
        return $this->root()->getNode($name);
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
