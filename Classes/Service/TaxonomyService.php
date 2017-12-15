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

use Sitegeist\Taxonomy\Package;
use Sitegeist\Taxonomy\Service\DimensionService;

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
     * @var NodeInterface[]
     */
    protected $taxoniomyDataRootNodes = [];

    /**
     * @var DimensionService
     * @Flow\Inject
     */
    protected $dimensionService;

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
     * @return string
     */
    public function getTaxonomyNodeType()
    {
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
        if (array_key_exists($contextHash, $this->taxoniomyDataRootNodes) && $this->taxoniomyDataRootNodes[$contextHash] instanceof NodeInterface) {
            return $this->taxoniomyDataRootNodes[$contextHash];
        }

        // return existing root-node
        $taxonomyDataRootNodeData = $this->nodeDataRepository->findOneByPath('/' . Package::ROOT_NODE_NAME, $context->getWorkspace());
        if ($taxonomyDataRootNodeData !== null) {
            $this->taxoniomyDataRootNodes[$contextHash] = $this->nodeFactory->createFromNodeData($taxonomyDataRootNodeData, $context);
            return $this->taxoniomyDataRootNodes[$contextHash];;
        }

        // create root-node
        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($this->rootNodeType));
        $nodeTemplate->setName(Package::ROOT_NODE_NAME);

        $rootNode = $context->getRootNode();
        $this->taxoniomyDataRootNodes[$contextHash] = $rootNode->createNodeFromTemplate($nodeTemplate);

        // persist root node
        $this->taxoniomyDataRootNodes[$contextHash]->getContext()->getWorkspace();
        $this->persistenceManager->persistAll();

        return $this->taxoniomyDataRootNodes[$contextHash];;
    }

    /**
     * @param string $vocabularyName
     * @param Context|null $context
     * @param $vocabulary
     */
    public function getVocabulary($vocabularyName, Context $context = null) {
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
    public function getTaxonomy($vocabularyName, $taxonomyPath, Context $context = null) {
        $vocabulary = $this->getVocabulary($vocabularyName);
        if ($vocabulary) {
            return $vocabulary->getNode($taxonomyPath);
        }
    }


}
