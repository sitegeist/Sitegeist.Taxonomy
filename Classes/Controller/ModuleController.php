<?php
namespace Sitegeist\Taxonomy\Controller;

/**
 * This file is part of the Sitegeist.Taxonomies package
 *
 * (c) 2016
 * Martin Ficzel <ficzel@sitegeist.de>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Error\Messages\Error;
use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Mvc\Controller\ActionController;
use Sitegeist\Taxonomy\Eel\TaxonomyHelper;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Utility as CrUtitlity;

/**
 * Class ModuleController
 * @package Sitegeist\Monocle\Controller
 */
class ModuleController extends ActionController
{

    /**
     * @Flow\Inject
     * @var TaxonomyHelper
     */
    protected $taxonomyHelper;

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
     * Initialize the view
     *
     * @param  ViewInterface $view
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {

    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $context = $this->contextFactory->create();
        $taxonomyRoot = $this->taxonomyHelper->root($context);
        $flowQuery = new FlowQuery([$taxonomyRoot]);
        $vocabularies = $flowQuery->children('[instanceof Sitegeist.Taxonomy:Vocabulary]')->get();
        $this->view->assign('vocabularies', $vocabularies);
    }

    /**
     *
     */
    public function newVocabularyAction()
    {
    }

    /**
     * @param string $title
     * @param string $description
     */
    public function createVocabularyAction($title, $description = '')
    {
        $context = $this->contextFactory->create();
        $taxonomyRoot = $this->taxonomyHelper->root($context);

        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($this->vocabularyNodeType));
        $nodeTemplate->setName(CrUtitlity::renderValidNodeName($title));

        $vocabularyNode = $taxonomyRoot->createNodeFromTemplate($nodeTemplate);
        $vocabularyNode->setProperty('title', $title);
        $vocabularyNode->setProperty('description', $description);

        $this->flashMessageContainer->addMessage(new Message(sprintf('created vocabulary %s' , $title)));
        $this->redirect('index');
    }

    /**
     * @param NodeInterface $vocabulary
     */
    public function showVocabularyAction(NodeInterface $vocabulary) {
        $this->view->assign('vocabulary', $vocabulary);
    }

    /**
     * @param NodeInterface $parent
     */
    public function newTaxonomyAction(NodeInterface $parent)
    {
        $this->view->assign('parent', $parent);
    }

    /**
     * @param NodeInterface $parent
     * @param string $title
     * @param string $description
     */
    public function createTaxonomyAction(NodeInterface $parent, $title, $description = '')
    {
        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($this->taxonomyNodeType));
        $nodeTemplate->setName(CrUtitlity::renderValidNodeName($title));

        $taxonomyNode = $parent->createNodeFromTemplate($nodeTemplate);
        $taxonomyNode->setProperty('title', $title);
        $taxonomyNode->setProperty('description', $description);

        $this->flashMessageContainer->addMessage(new Message(sprintf('created vocabulary %s' , $title)));

        $flowQuery = new FlowQuery([$taxonomyNode]);
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->vocabularyNodeType . ']')->get(0);

        $this->redirect('showVocabulary', null, null, ['vocabulary' => $vocabulary->getContextPath()]);
    }

}
