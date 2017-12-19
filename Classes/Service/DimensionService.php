<?php
namespace Sitegeist\Taxonomy\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Utility\Arrays;
use Neos\ContentRepository\Service\FallbackGraphService;
use Neos\ContentRepository\Domain\Model\InterDimension\ContentSubgraph;
use Neos\ContentRepository\Domain\Model\IntraDimension\ContentDimensionValue;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 * Class DimensionService
 * @package Sitegeist\Taxonomy\Service
 * @Flow\Scope("singleton")
 */
class DimensionService
{
    /**
     * @var array
     * @Flow\InjectConfiguration(package="Neos.ContentRepository", path="contentDimensions")
     */
    protected $contentDimensionSettings;

    /**
     * @Flow\Inject
     * @var FallbackGraphService
     */
    protected $fallbackGraphService;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @return array|ContentSubgraph[]
     */
    public function getBaseDimensionSubgraphs()
    {
        $interDimensionalFallbackGraph = $this->fallbackGraphService->getInterDimensionalFallbackGraph();
        $subgraphs = $interDimensionalFallbackGraph->getSubgraphs();
        if (count($subgraphs) == 1) {
            return [];
        }
        // find all dimensionGraphs that have no fallbacks
        $baseDimensionGraphs = array_filter($subgraphs, function ($subgraph) {
            return (array_sum($subgraph->getWeight()) === 0);
        });
        return $baseDimensionGraphs;
    }

    /**
     * @return array|ContentSubgraph[]
     */
    public function getAllDimensionSubgraphs()
    {
        $interDimensionalFallbackGraph = $this->fallbackGraphService->getInterDimensionalFallbackGraph();
        return $interDimensionalFallbackGraph;
    }

    /**
     * @param NodeInterface $node
     * @return NodeInterface[] new variants;
     */
    public function ensureBaseVariantsExist(NodeInterface $node)
    {
        $results = [];
        $baseDimensionSubgraphs = $this->getBaseDimensionSubgraphs();
        if (count($baseDimensionSubgraphs) > 0) {
            $nodeContext = $node->getContext();
            foreach ($baseDimensionSubgraphs as $baseDimensionSubgraph) {
                $baseDimensionValues = [
                    'dimensions' => array_map(
                        function (ContentDimensionValue $contentDimensionValue) {
                            return [$contentDimensionValue->getValue()];
                        },
                        $baseDimensionSubgraph->getDimensionValues()
                    ),
                    'targetDimensions' => array_map(
                        function (ContentDimensionValue $contentDimensionValue) {
                            return $contentDimensionValue->getValue();
                        },
                        $baseDimensionSubgraph->getDimensionValues()
                    ),
                ];

                $baseDimensionContext = array_merge($nodeContext->getProperties(), $baseDimensionValues);
                $modifiedContext = $this->contextFactory->create($baseDimensionContext);

                if ($modifiedContext->getNode($node->getPath()) == null) {
                    $results[] = $modifiedContext->adoptNode($node);
                    ;
                }
            }
        }
        $this->persistenceManager->persistAll();
        return $results;
    }
}
