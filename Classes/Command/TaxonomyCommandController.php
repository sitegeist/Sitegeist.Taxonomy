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
use Neos\ContentRepository\Core\Projection\ContentGraph\NodePath;
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
    public function vocabulariesCommand(): void
    {
        $subgraph = $this->taxonomyService->getDefaultSubgraph();
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
     * List taxonomies inside a vocabulary
     *
     * @param string $vocabulary name of the vocabulary to access
     * @param string $path path to the taxonomy starting at the vocabulary
     */
    public function taxonomiesCommand(string $vocabulary, string $path = ''): void
    {
        $subgraph = $this->taxonomyService->getDefaultSubgraph();

        if ($path) {
            $startPoint = $this->taxonomyService->findTaxonomyByVocabularyNameAndPath($subgraph, $vocabulary, $path);
        } else {
            $startPoint = $this->taxonomyService->findVocabularyByName($subgraph, $vocabulary);
            ;
        }

        if (!$startPoint) {
            $this->outputLine('nothing found');
            $this->quit(1);
        }

        $subtree = $this->taxonomyService->findSubtree($startPoint);
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
