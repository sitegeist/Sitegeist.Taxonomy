<?php

/**
 * This file is part of the Sitegeist.Taxonomies package
 *
 * (c) 2017
 * Martin Ficzel <ficzel@sitegeist.de>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

declare(strict_types=1);

namespace Sitegeist\Taxonomy\Eel;

use Neos\ContentRepository\Core\Projection\ContentGraph\ContentSubgraphInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Annotations as Flow;
use Sitegeist\Taxonomy\Service\TaxonomyService;
use Neos\Eel\ProtectedContextAwareInterface;

class TaxonomyHelper implements ProtectedContextAwareInterface
{
    /**
     * @var TaxonomyService
     * @Flow\Inject
     */
    protected $taxonomyService;

    public function root(ContentSubgraphInterface $subgraph = null): Node
    {
        return $this->taxonomyService->findOrCreateRoot($subgraph);
    }

    public function vocabulary(ContentSubgraphInterface $subgraph, string $vocabulary): ?Node
    {
        return $this->taxonomyService->findVocabularyByName($subgraph, $vocabulary);
    }

    public function taxonomy(ContentSubgraphInterface $subgraph, string $vocabulary, string $path): ?Node
    {
        return $this->taxonomyService->findTaxonomyByVocabularyNameAndPath($subgraph, $vocabulary, $path);
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
