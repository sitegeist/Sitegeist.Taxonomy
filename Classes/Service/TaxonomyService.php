<?php
namespace Sitegeist\Taxonomy\Service;

use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;

use Neos\ContentRepository\Domain\Factory\NodeFactory;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\ContentRepository\Domain\Service\Context;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Log\SystemLoggerInterface;

/**
 * Class TaxonomyService
 * @package Sitegeist\Taxonomy\Service
 * @Flow\Scope("singleton")
 */
class TaxonomyService
{

    /**
     * @var SystemLoggerInterface
     * @Flow\Inject
     */
    protected $systemLogger;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="contentRepository.rootNodeName")
     */
    protected $rootNodeName;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="contentRepository.rootNodeType")
     */
    protected $rootNodeType;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="contentRepository.vocabularyNodeType")
     */
    protected $vocabularyNodeType;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="contentRepository.taxonomyNodeType")
     */
    protected $taxonomyNodeType;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="contentRepository.taxonomyNodeTypes")
     */
    protected $taxonomyNodeTypes;

    /**
     * @var NodeInterface[]
     */
    protected $taxonomyDataRootNodes = [];

    /**
     * @return string
     */
    public function getRootNodeName()
    {
        return $this->rootNodeName;
    }

    /**
     * @return string
     */
    public function getRootNodeType()
    {
        return $this->rootNodeType;
    }

    /**
     * @return string
     */
    public function getVocabularyNodeType()
    {
        return $this->vocabularyNodeType;
    }

    /**
     * @param NodeInterface $parent
     * @return string
     */
    public function getTaxonomyNodeType(NodeInterface $parent = null)
    {
        if ($parent !== null) {
            $currentVocabularyNodeType = null;
            do {
                if ($parent->getNodeType()->isOfType($this->getVocabularyNodeType())) {
                    $currentVocabularyNodeType = $parent->getNodeType()->getName();
                } else {
                    $parent = $parent->getParent();
                }
            } while ($parent !== null && $currentVocabularyNodeType === null);

            if ($currentVocabularyNodeType !== null && !empty($this->taxonomyNodeTypes[$currentVocabularyNodeType])) {
                return $this->taxonomyNodeTypes[$currentVocabularyNodeType];
            }
        }
        return $this->taxonomyNodeType;
    }

    /**
     * @param Context $context
     * @return NodeInterface
     */
    public function getRoot(Context $context = null)
    {
        if ($context === null) {
            $context = $this->contextFactory->create();
        }

        $contextHash = md5(json_encode($context->getProperties()));

        // return memoized root-node
        if (array_key_exists($contextHash, $this->taxonomyDataRootNodes)
            && $this->taxonomyDataRootNodes[$contextHash] instanceof NodeInterface
        ) {
            return $this->taxonomyDataRootNodes[$contextHash];
        }

        // return existing root-node
        //
        // TODO: Find a better way to determine the root node
        $taxonomyDataRootNodeData = $this->nodeDataRepository->findOneByPath(
            '/' . $this->getRootNodeName(),
            $context->getWorkspace()
        );

        if ($taxonomyDataRootNodeData !== null) {
            $this->taxonomyDataRootNodes[$contextHash] = $this->nodeFactory->createFromNodeData(
                $taxonomyDataRootNodeData,
                $context
            );

            return $this->taxonomyDataRootNodes[$contextHash];
        }

        // create root-node
        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($this->rootNodeType));
        $nodeTemplate->setName($this->getRootNodeName());

        $rootNode = $context->getRootNode();
        $this->taxonomyDataRootNodes[$contextHash] = $rootNode->createNodeFromTemplate($nodeTemplate);

        // We fetch the workspace to be sure it's known to the persistence manager and persist all
        // so the workspace and site node are persisted before we import any nodes to it.
        $this->taxonomyDataRootNodes[$contextHash]->getContext()->getWorkspace();
        $this->persistenceManager->persistAll();

        return $this->taxonomyDataRootNodes[$contextHash];
    }

    /**
     * @param string $vocabularyName
     * @param Context|null $context
     * @param $vocabulary
     */
    public function getVocabulary($vocabularyName, Context $context = null)
    {
        if ($context === null) {
            $context = $this->contextFactory->create();
        }

        $root = $this->getRoot();
        return $root->getNode($vocabularyName);
    }

    /**
     * @param string $vocabularyName
     * @param string $taxonomyPath
     * @param Context|null $context
     * @param $vocabulary
     */
    public function getTaxonomy($vocabularyName, $taxonomyPath, Context $context = null)
    {
        $vocabulary = $this->getVocabulary($vocabularyName, $context);
        if ($vocabulary) {
            return $vocabulary->getNode($taxonomyPath);
        }
    }
}
