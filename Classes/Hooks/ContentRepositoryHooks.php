<?php
namespace Sitegeist\Taxonomy\Hooks;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Sitegeist\Taxonomy\Service\TaxonomyService;
use Sitegeist\Taxonomy\Service\DimensionService;

/**
 * Class ContentRepositoryHooks
 * @package Sitegeist\Taxonomy\Hooks
 * @Flow\Scope("singleton")
 */
class ContentRepositoryHooks
{

    /**
     * @var TaxonomyService
     * @Flow\Inject
     */
    protected $taxonomyService;

    /**
     * @var DimensionService
     * @Flow\Inject
     */
    protected $dimensionService;

    /**
     * @var bool
     */
    protected $preventCascade = false;

    /**
     * Signal that is triggered on node create
     *
     * @param NodeInterface $node
     */
    public function nodeAdded(NodeInterface $node)
    {
        if ($node->getNodeType()->isOfType($this->taxonomyService->getRootNodeType()) ||
            $node->getNodeType()->isOfType($this->taxonomyService->getVocabularyNodeType()) ||
            $node->getNodeType()->isOfType($this->taxonomyService->getTaxonomyNodeType())) {
            if ($node->isAutoCreated() == false && $this->preventCascade == false) {
                $this->preventCascade = true;
                $this->dimensionService->ensureBaseVariantsExist($node);
                $this->preventCascade = false;
            }
        }
    }

    /**
     * Signal that is triggered on node remove
     *
     * @param NodeInterface $node
     */
    public function nodeRemoved(NodeInterface $node)
    {
        if ($node->getNodeType()->isOfType($this->taxonomyService->getRootNodeType()) ||
            $node->getNodeType()->isOfType($this->taxonomyService->getVocabularyNodeType()) ||
            $node->getNodeType()->isOfType($this->taxonomyService->getTaxonomyNodeType())) {
            if ($node->isAutoCreated() == false && $this->preventCascade == false) {
                $this->preventCascade = true;
                $this->dimensionService->removeOtherVariants($node);
                $this->preventCascade = false;
            }
        }
    }
}
