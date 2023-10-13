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

namespace Sitegeist\Taxonomy\Service;

use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\Factory\ContentRepositoryId;
use Neos\ContentRepository\Core\Feature\RootNodeCreation\Command\CreateRootNodeAggregateWithNode;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\NodeType\NodeTypeNames;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentSubgraphInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindAncestorNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindSubtreeFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\NodePath;
use Neos\ContentRepository\Core\Projection\ContentGraph\Nodes;
use Neos\ContentRepository\Core\Projection\ContentGraph\NodeTypeConstraints;
use Neos\ContentRepository\Core\Projection\ContentGraph\Subtree;
use Neos\ContentRepository\Core\Projection\ContentGraph\VisibilityConstraints;
use Neos\ContentRepository\Core\Projection\Workspace\Workspace;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeName;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Exception\LiveWorkspaceIsMissing;
use Neos\Neos\FrontendRouting\NodeAddressFactory;
use Sitegeist\Taxonomy\Constants;

class TaxonomyService
{
    #[Flow\Inject(lazy:false)]
    protected ContentRepositoryRegistry $crRegistry;

    protected ContentRepository|null $contentRepository = null;

    /**
     * @var mixed[]
     */
    #[Flow\InjectConfiguration]
    protected array $configuration = [];

    public function getRootNodeTypeName(): NodeTypeName
    {
        return NodeTypeName::fromString('Sitegeist.Taxonomy:Root');
    }

    public function getVocabularyNodeTypeName(): NodeTypeName
    {
        return NodeTypeName::fromString('Sitegeist.Taxonomy:Vocabulary');
    }

    public function getTaxonomyNodeTypeName(): NodeTypeName
    {
        return NodeTypeName::fromString('Sitegeist.Taxonomy:Taxonomy');
    }

    public function getContentRepository(): ContentRepository
    {
        if (is_null($this->contentRepository)) {
            $crid = $this->configuration['contentRepository']['identifier'] ?? null;
            if (!is_string($crid)) {
                throw new \InvalidArgumentException();
            }
            $this->contentRepository = $this->crRegistry->get(ContentRepositoryId::fromString($crid));
        }
        return $this->contentRepository;
    }

    public function findVocabularyForNode(Node $node): Node
    {
        $subgraph = $this->crRegistry->subgraphForNode($node);

        $ancestors = $subgraph->findAncestorNodes(
            $node->nodeAggregateId,
            FindAncestorNodesFilter::create(
                NodeTypeConstraints::create(
                    NodeTypeNames::fromArray([ $this->getVocabularyNodeTypeName()]),
                    NodeTypeNames::createEmpty()
                )
            )
        );

        if ($result = $ancestors->first()) {
            return $result;
        }

        throw new \InvalidArgumentException('node seems to be outside of vocabulary');
    }

    public function findOrCreateRoot(ContentSubgraphInterface $subgraph): Node
    {
        $rootNode = $subgraph->findRootNodeByType($this->getRootNodeTypeName());
        if ($rootNode instanceof Node) {
            return $rootNode;
        }

        $contentRepository = $this->getContentRepository();
        $liveWorkspace = $this->getLiveWorkspace();

        $commandResult = $contentRepository->handle(
            CreateRootNodeAggregateWithNode::create(
                $liveWorkspace->currentContentStreamId,
                NodeAggregateId::create(),
                $this->getRootNodeTypeName()
            )
        );
        $commandResult->block();

        $rootNode = $subgraph->findRootNodeByType($this->getRootNodeTypeName());
        if ($rootNode instanceof Node) {
            return $rootNode;
        }

        throw new \Exception('taxonomy root could neither be found nor created');
    }

    public function findAllVocabularies(ContentSubgraphInterface $subgraph): Nodes
    {
        $root = $this->findOrCreateRoot($subgraph);
        return $subgraph->findChildNodes(
            $root->nodeAggregateId,
            FindChildNodesFilter::create(
                NodeTypeConstraints::create(
                    NodeTypeNames::fromArray([$this->getVocabularyNodeTypeName()]),
                    NodeTypeNames::createEmpty()
                )
            )
        );
    }

    public function findVocabularyByName(ContentSubgraphInterface $subgraph, string $vocabularyName): ?Node
    {
        // @todo find root -> find named child
        $vocabularies = $this->findAllVocabularies($subgraph);
        foreach ($vocabularies as $vocabulary) {
            if ($vocabulary->nodeName?->value == $vocabularyName) {
                return $vocabulary;
            }
        }
        return null;
    }

    public function findTaxonomyByVocabularyNameAndPath(ContentSubgraphInterface $subgraph, string $vocabularyName, string $taxonomyPath): ?Node
    {
        $vocabulary = $this->findVocabularyByName($subgraph, $vocabularyName);
        if (!$vocabulary instanceof Node) {
            return null;
        }
        $taxonomy = $subgraph->findNodeByPath(
            NodePath::fromString($taxonomyPath),
            $vocabulary->nodeAggregateId
        );
        return $taxonomy;
    }

    public function findSubtree(Node $StartNode): ?Subtree
    {
        $subgraph = $this->crRegistry->subgraphForNode($StartNode);

        $vocabularySubtree = $subgraph->findSubtree(
            $StartNode->nodeAggregateId,
            FindSubtreeFilter::create(
                NodeTypeConstraints::create(
                    NodeTypeNames::fromArray([$this->getTaxonomyNodeTypeName(), $this->getVocabularyNodeTypeName()]),
                    NodeTypeNames::createEmpty()
                )
            )
        );

        return $vocabularySubtree ? $this->orderSubtreeByNameRecursive($vocabularySubtree) : null;
    }

    private function orderSubtreeByNameRecursive(Subtree $subtree): Subtree
    {
        $children = $subtree->children;
        $children = array_map(
            fn(Subtree $item) => $this->orderSubtreeByNameRecursive($item),
            $children
        );
        usort(
            $children,
            fn(Subtree $a, Subtree $b) => $a->node->nodeName?->value <=> $b->node->nodeName?->value
        );
        return new Subtree(
            $subtree->level,
            $subtree->node,
            $children
        );
    }

    public function getNodeByNodeAddress(string $serializedNodeAddress): Node
    {
        $contentRepository = $this->getContentRepository();
        $nodeAddress = NodeAddressFactory::create($contentRepository)->createFromUriString($serializedNodeAddress);
        $subgraph = $contentRepository->getContentGraph()->getSubgraph(
            $nodeAddress->contentStreamId,
            $nodeAddress->dimensionSpacePoint,
            VisibilityConstraints::withoutRestrictions()
        );
        $node = $subgraph->findNodeById($nodeAddress->nodeAggregateId);
        if (is_null($node)) {
            throw new \InvalidArgumentException('nodeAddress does not resolve to a node');
        }
        return $node;
    }

    public function getDefaultSubgraph(): ContentSubgraphInterface
    {
        $contentRepository = $this->getContentRepository();
        $liveWorkspace = $this->getLiveWorkspace();
        $generalizations = $contentRepository->getVariationGraph()->getRootGeneralizations();
        $dimensionSpacePoint = reset($generalizations);
        if (!$dimensionSpacePoint) {
            throw new \Exception('default dimensionSpacePoint could not be found');
        }
        $contentGraph = $contentRepository->getContentGraph();
        $subgraph = $contentGraph->getSubgraph(
            $liveWorkspace->currentContentStreamId,
            $dimensionSpacePoint,
            VisibilityConstraints::withoutRestrictions()
        );
        return $subgraph;
    }

    public function getSubgraphForNode(Node $node): ContentSubgraphInterface
    {
        return $this->crRegistry->subgraphForNode($node);
    }

    public function getLiveWorkspace(): Workspace
    {
        $liveWorkspace = $this->getContentRepository()->getWorkspaceFinder()->findOneByName(WorkspaceName::forLive());
        if (!$liveWorkspace) {
            throw LiveWorkspaceIsMissing::butWasRequested();
        }
        return $liveWorkspace;
    }
}
