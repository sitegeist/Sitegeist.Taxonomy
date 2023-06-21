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
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindSubtreeFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\NodePath;
use Neos\ContentRepository\Core\Projection\ContentGraph\Nodes;
use Neos\ContentRepository\Core\Projection\ContentGraph\NodeTypeConstraints;
use Neos\ContentRepository\Core\Projection\ContentGraph\Subtree;
use Neos\ContentRepository\Core\Projection\ContentGraph\VisibilityConstraints;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeName;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\FrontendRouting\NodeAddressFactory;

/**
 * Class TaxonomyService
 * @package Sitegeist\Taxonomy\Service
 * @Flow\Scope("singleton")
 */
class TaxonomyService
{
    /**
     * @Flow\Inject
     * @var ContentRepositoryRegistry
     */
    protected $crRegistry;

    /**
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * @var array
     * @Flow\InjectConfiguration()
     */
    protected array $configuration = [];

    public function getRootNodeTypeName(): NodeTypeName
    {
        return NodeTypeName::fromString(
            $this->configuration['contentRepository']['rootNodeType'] ?? 'Sitegeist.Taxonomy:Root'
        );
    }

    public function getVocabularyNodeTypeName(): NodeTypeName
    {
        return NodeTypeName::fromString(
            $this->configuration['contentRepository']['vocabularyNodeType'] ?? 'Sitegeist.Taxonomy:Vocabulary'
        );
    }

    public function getTaxonomyNodeTypeName(): NodeTypeName
    {
        return NodeTypeName::fromString(
            $this->configuration['contentRepository']['taxonomyNodeType'] ?? 'Sitegeist.Taxonomy:Taxonomy'
        );
    }

    public function getContentRepository(): ContentRepository
    {
        if (is_null($this->contentRepository)) {
            $this->contentRepository = $this->crRegistry->get(
                ContentRepositoryId::fromString($this->configuration['contentRepository']['identifier'] ?? 'default')
            );
        }
        return $this->contentRepository;
    }

    public function findVocabularyForNode(Node $node): Node
    {
        $subgraph = $this->getContentRepository()->getContentGraph()->getSubgraph(
            $node->subgraphIdentity->contentStreamId,
            $node->subgraphIdentity->dimensionSpacePoint,
            $node->subgraphIdentity->visibilityConstraints,
        );

        $parentNode = $node;
        while ($parentNode instanceof Node) {
            if ($parentNode->nodeType->isOfType($this->getVocabularyNodeTypeName()->value)) {
                return $parentNode;
            }
            $parentNode = $subgraph->findParentNode($parentNode->nodeAggregateId);
        }
        throw new \InvalidArgumentException('Node seems to be outside of vocabulary');
    }

    public function findOrCreateRoot(ContentSubgraphInterface $subgraph): Node
    {
        $rootNode = $subgraph->findRootNodeByType($this->getRootNodeTypeName());
        if ($rootNode instanceof Node) {
            return $rootNode;
        }

        $contentRepository = $this->getContentRepository();
        $liveWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(WorkspaceName::forLive());

        $commandResult = $contentRepository->handle(
            new CreateRootNodeAggregateWithNode(
                $liveWorkspace->currentContentStreamId,
                NodeAggregateId::create(),
                $this->getRootNodeTypeName()
            )
        );
        $commandResult->block();

        return $subgraph->findRootNodeByType($this->getRootNodeTypeName());
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
        $vocabularies = $this->findAllVocabularies($subgraph);
        foreach ($vocabularies as $vocabulary) {
            if ($vocabulary->nodeName->value == $vocabularyName) {
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

    public function findSubtree(Node $StartNode): Subtree
    {
        $contentRepository = $this->getContentRepository();
        $subgraph = $contentRepository->getContentGraph()->getSubgraph(
            $StartNode->subgraphIdentity->contentStreamId,
            $StartNode->subgraphIdentity->dimensionSpacePoint,
            $StartNode->subgraphIdentity->visibilityConstraints,
        );

        $vocabularySubtree = $subgraph->findSubtree(
            $StartNode->nodeAggregateId,
            FindSubtreeFilter::create(
                NodeTypeConstraints::create(
                    NodeTypeNames::fromArray([$this->getTaxonomyNodeTypeName(), $this->getVocabularyNodeTypeName()]),
                    NodeTypeNames::createEmpty()
                )
            )
        );

        return $this->orderSubtreeByNameRecursive($vocabularySubtree);
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
            fn(Subtree $a, Subtree $b) => $a->node->nodeName->value <=> $b->node->nodeName->value
        );
        return new Subtree(
            $subtree->level,
            $subtree->node,
            $children
        );
    }

    public function getNodeByNodeAddress(?string $serializedNodeAddress): ?Node
    {
        $contentRepository = $this->getContentRepository();
        $nodeAddress = NodeAddressFactory::create($contentRepository)->createFromUriString($serializedNodeAddress);
        $subgraph = $contentRepository->getContentGraph()->getSubgraph(
            $nodeAddress->contentStreamId,
            $nodeAddress->dimensionSpacePoint,
            VisibilityConstraints::withoutRestrictions()
        );
        return $subgraph->findNodeById($nodeAddress->nodeAggregateId);
    }

    public function getDefaultSubgraph(): ContentSubgraphInterface
    {
        $contentRepository = $this->getContentRepository();
        $liveWorkspace = $contentRepository->getWorkspaceFinder()->findOneByName(WorkspaceName::forLive());
        $generalizations = $contentRepository->getVariationGraph()->getRootGeneralizations();
        $contentGraph = $contentRepository->getContentGraph();
        $subgraph = $contentGraph->getSubgraph(
            $liveWorkspace->currentContentStreamId,
            reset($generalizations),
            VisibilityConstraints::withoutRestrictions()
        );
        return $subgraph;
    }

    public function getSubgraphForNode(Node $node): ContentSubgraphInterface
    {
        return $this->crRegistry->subgraphForNode($node);
    }
}
