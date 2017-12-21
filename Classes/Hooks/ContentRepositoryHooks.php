<?php
namespace Sitegeist\Taxonomy\Hooks;

use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Log\SystemLoggerInterface;
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
     * @var SystemLoggerInterface
     * @Flow\Inject
     */
    protected $systemLogger;

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
        $this->systemLogger->log(new Message(sprintf("CREATED NODE %S", $node->getContextPath())));

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
}
