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
        return $this->taxonomyService->getRoot($context);
    }

    /**
     * @param string $vocabulary Name of the vocabulary node
     * @param ContentContext $context
     * @return NodeInterface
     */
    public function vocabulary($vocabulary, ContentContext $context = null)
    {
        return $this->taxonomyService->findVocabulary($vocabulary, $context);
    }

    /**
     * @param string $vocabulary Name of the vocabulary node
     * @param string $path Path of the taxonomy node
     * @param ContentContext $context
     * @return NodeInterface
     */
    public function taxonomy($vocabulary, $path, ContentContext $context = null)
    {
        return $this->taxonomyService->getTaxonomy($vocabulary, $path, $context);
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
