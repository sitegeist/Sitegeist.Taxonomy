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

        $vocabulary = $taxonomyRoot->createNodeFromTemplate($nodeTemplate);
        $vocabulary->setProperty('title', $title);
        $vocabulary->setProperty('description', $description);

        $this->flashMessageContainer->addMessage(new Message(sprintf('Created vocabulary %s' , $title)));
        $this->redirect('index');
    }

    /**
     * @param NodeInterface $vocabulary
     */
    public function vocabularyAction(NodeInterface $vocabulary) {
        $this->view->assign('vocabulary', $vocabulary);
    }

    /**
     * @param NodeInterface $vocabulary
     */
    public function editVocabularyAction(NodeInterface $vocabulary) {
        $this->view->assign('vocabulary', $vocabulary);
    }


    /**
     * @param NodeInterface $vocabulary
     * @param string $title
     * @param string $description
     */
    public function updateVocabularyAction(NodeInterface $vocabulary, $title, $description = '')
    {
        $previousTitle = $vocabulary->getProperty('title');
        $previousDescription = $vocabulary->getProperty('description');

        if ($previousTitle !== $title) {
            $vocabulary->setProperty('title', $title);
            if ($vocabulary->isAutoCreated() === false) {
                $possibleName = CrUtitlity::renderValidNodeName($title);
                if ($vocabulary->getName() !== $possibleName) {
                    $vocabulary->setName($possibleName);
                }
            }
        }

        if ($previousDescription !== $description) {
            $vocabulary->setProperty('description', $description);
        }

        $this->flashMessageContainer->addMessage(new Message(sprintf('Updated vocabulary %s' , $title)));
        $this->redirect('index');
    }

    /**
     * @param NodeInterface $vocabulary
     */
    public function deleteVocabularyAction(NodeInterface $vocabulary) {
        if ($vocabulary->isAutoCreated()) {
            throw new \Exception('cannot delete autocrated vocabularies');
        } else {
            $path = $vocabulary->getPath();
            $vocabulary->remove();
            $this->flashMessageContainer->addMessage(new Message(sprintf('Deleted vocabulary %s' , $path)));
        }
        $this->redirect('index');
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

        $taxonomy = $parent->createNodeFromTemplate($nodeTemplate);
        $taxonomy->setProperty('title', $title);
        $taxonomy->setProperty('description', $description);

        $this->flashMessageContainer->addMessage(new Message(sprintf('Created taxonomy %s' , $taxonomy->getPath() )));

        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->vocabularyNodeType . ']')->get(0);

        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary->getContextPath()]);
    }

    /**
     * @param NodeInterface $taxonomy
     */
    public function editTaxonomyAction(NodeInterface $taxonomy)
    {
        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->vocabularyNodeType . ']')->get(0);

        $this->view->assign('vocabulary', $vocabulary);
        $this->view->assign('taxonomy', $taxonomy);

    }

    /**
     * @param NodeInterface $taxonomy
     * @param string $title
     * @param string $description
     */
    public function updateTaxonomyAction(NodeInterface $taxonomy, $title, $description = '')
    {
        $previousTitle = $taxonomy->getProperty('title');
        $previousDescription = $taxonomy->getProperty('description');

        if ($previousTitle !== $title) {
            $taxonomy->setProperty('title', $title);
            if ($taxonomy->isAutoCreated() === false) {
                $possibleName = CrUtitlity::renderValidNodeName($title);
                if ($taxonomy->getName() !== $possibleName) {
                    $taxonomy->setName($possibleName);
                }
            }
        }

        if ($previousDescription !== $description) {
            $taxonomy->setProperty('description', $description);
        }

        $this->flashMessageContainer->addMessage(new Message(sprintf('Updated taxonomy %s' , $taxonomy->getPath() )));

        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->vocabularyNodeType . ']')->get(0);

        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary->getContextPath()]);
    }

    /**
     * @param NodeInterface $taxonomy
     */
    public function deleteTaxonomyAction(NodeInterface $taxonomy)
    {
        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->vocabularyNodeType . ']')->get(0);

        if ($taxonomy->isAutoCreated()) {
            throw new \Exception('cannot delete autocrated vocabularies');
        } else {
            $path = $taxonomy->getPath();
            $taxonomy->remove();
            $this->flashMessageContainer->addMessage(new Message(sprintf('Deleted taxonomy %s' , $path)));
        }
        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary]);
    }
}
