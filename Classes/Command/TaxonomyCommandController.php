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

namespace Sitegeist\Taxonomy\Command;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\Subtree;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Sitegeist\Taxonomy\Service\TaxonomyService;

/**
 * @Flow\Scope("singleton")
 */
class TaxonomyCommandController extends CommandController
{
    /**
     * @var TaxonomyService
     * @Flow\Inject
     */
    protected $taxonomyService;

    /**
     * List all vocabularies
     */
    public function listVocabulariesCommand(): void
    {
        $subgraph = $this->taxonomyService->findSubgraph();
        $vocabularies = $this->taxonomyService->findAllVocabularies($subgraph);
        $this->output->outputTable(
            array_map(
                fn(Node $node) => [$node->nodeName->value, $node->getProperty('title'), $node->getProperty('description')],
                iterator_to_array($vocabularies->getIterator())
            ),
            ['name', 'title', 'description']
        );
    }

    /**
     * List all taxonomies of a vocabulary
     *
     * @param string $path path to the taxonomy starting with the vocabulary name (separated with dots)
     */
    public function listTaxonomiesCommand(string $path): void
    {
        $subgraph = $this->taxonomyService->findSubgraph();
        $node = $this->taxonomyService->findVocabularyOrTaxonomyByPath($subgraph, explode('.', $path));
        if (!$node) {
            $this->outputLine('nothing found');
            $this->quit(1);
        }
        $subtree = $this->taxonomyService->findSubtree($node);
        $this->output->outputTable(
            $this->subtreeToTableRowsRecursively($subtree),
            ['name', 'title', 'description']
        );
    }

    private function subtreeToTableRowsRecursively(Subtree $subtree): array
    {
        $childRows = array_map(fn(Subtree $subtree)=>$this->subtreeToTableRowsRecursively($subtree), $subtree->children);
        $row = [str_repeat('  ', $subtree->level) . $subtree->node->nodeName->value, $subtree->node->getProperty('title'), $subtree->node->getProperty('description')];
        return [$row, ...array_merge(...$childRows)];
    }
}
