<?php

namespace Sitegeist\Taxonomy\Eel;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class TaxonomyHelper
{

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
}
