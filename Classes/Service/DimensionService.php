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
        // find all dimensionGraphs that have no fallbacks
        $baseDimensionGraphs = array_filter(
            $interDimensionalFallbackGraph->getSubgraphs(),
            function ($subgraph) {
                try {
                    $weight = $subgraph->getWeight();
                    return (array_sum($weight) === 0);
                } catch (\TypeError $e) {
                    // TODO:
                    // Yep, we're catching a TypeError here. That's because
                    // $subgraph->getWeight() is supposed to return an array, but it doesn't if
                    // there's no dimension configuration at all. This sure will be fixed in
                    // future releases of the content repository and should be adjusted at this
                    // point as well.
                    return true;
                }
            }
        );

        return $baseDimensionGraphs;
    }

    /**
     * @return array|ContentSubgraph[]
     */
    public function getDimensionSubgraphByTargetValues($targetValues)
    {
        $interDimensionalFallbackGraph = $this->fallbackGraphService->getInterDimensionalFallbackGraph();
        $allSubgraphs = $interDimensionalFallbackGraph->getSubgraphs();
        $matchingSubgraphs = array_filter(
            $allSubgraphs,
            function($subgraph) use ($targetValues){
                foreach($subgraph->getDimensionValues() as $targetDimension => $targetValue) {
                    $value = (string) $subgraph->getDimensionValue($targetDimension);
                    if (!array_key_exists($targetDimension, $targetValues)) {
                        return false;
                    }
                    if ($targetValues[$targetDimension] != $value) {
                        return false;
                    }
                }
                return true;
            }
        );

        if (count($matchingSubgraphs) == 1) {
            return array_pop($matchingSubgraphs);
        }
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
                $baseDimensionValues = $this->getDimensionValuesForSubgraph($baseDimensionSubgraph);
                $baseDimensionContext = array_merge($nodeContext->getProperties(), $baseDimensionValues);
                $targetContext = $this->contextFactory->create($baseDimensionContext);
                $adoptedNode = $targetContext->adoptNode($node, true);
                $results[] = $adoptedNode;
            }
        }
        $this->persistenceManager->persistAll();
        return $results;
    }

    /**
     * @param NodeInterface $node
     * @return NodeInterface[] removed variants;
     */
    public function removeOtherVariants(NodeInterface $node)
    {
        $results = [];
        /**
         * @var array<NodeInterface> $otherNodeVariants
         */
        $otherNodeVariants = $node->getOtherNodeVariants();
        foreach ($otherNodeVariants as $nodeVariant) {
            /**
             * @var NodeInterface $nodeVariant
             */
            $nodeVariant->remove();
            $results[] = $nodeVariant;
        }
        $this->persistenceManager->persistAll();
        return $results;
    }

    /**
     * @param ContentSubgraph $baseDimensionSubgraph
     * @return array
     */
    public function getDimensionValuesForSubgraph(ContentSubgraph $baseDimensionSubgraph): array
    {
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
        return $baseDimensionValues;
    }
}
