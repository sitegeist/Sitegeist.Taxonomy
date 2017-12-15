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
use Sitegeist\Taxonomy\Service\DimensionService;
use Sitegeist\Taxonomy\Service\TaxonomyService;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Utility as CrUtitlity;
use Neos\Utility\Arrays;

/**
 * Class ModuleController
 * @package Sitegeist\Monocle\Controller
 */
class ModuleController extends ActionController
{

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
     * @Flow\InjectConfiguration(package="Neos.ContentRepository", path="contentDimensions")
     */
    protected $contentDimensions;

    /**
     * @var DimensionService
     * @Flow\Inject
     */
    protected $dimensionService;

    /**
     * @var TaxonomyService
     * @Flow\Inject
     */
    protected $taxonomyService;

    /**
     * Initialize the view
     *
     * @param  ViewInterface $view
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
        $this->view->assign('contentDimensionOptions', $this->getContentDimensionOptions());
    }

    /**
     * @param NodeInterface $root
     * @return void
     */
    public function indexAction ( NodeInterface $root = null)
    {
        if (!$root) {
            $root = $this->taxonomyService->getRoot();
        }

        $flowQuery = new FlowQuery([$root]);
        $vocabularies = $flowQuery->children('[instanceof Sitegeist.Taxonomy:Vocabulary]')->get();

        $this->view->assign('taxonomyRoot', $root);
        $this->view->assign('vocabularies', $vocabularies);
    }

    /**
     * @param string $targetAction
     * @param string $targetProperty
     * @param NodeInterface $contextNode
     * @param array $dimensions
     */
    public function changeContextAction($targetAction, $targetProperty, NodeInterface $contextNode, $dimensions = []) {
        $contextProperties = $contextNode->getContext()->getProperties();

        $newContextProperties = [];
        foreach($dimensions as $dimensionName => $presetName) {
            $newContextProperties['dimensions'][$dimensionName] = $this->getContentDimensionValues($dimensionName, $presetName);
            $newContextProperties['targetDimensions'][$dimensionName] = $presetName;
        }
        $modifiedContext = $this->contextFactory->create( array_merge($contextProperties, $newContextProperties));
        $nodeInModifiedContext = $modifiedContext->getNodeByIdentifier($contextNode->getIdentifier());

        $this->redirect($targetAction, null, null, [$targetProperty => $nodeInModifiedContext]);
    }

    protected function getContentDimensionOptions ()
    {
        $result = [];

        if (is_array($this->contentDimensions) === FALSE || count($this->contentDimensions) === 0 ) {
            return $result;
        }

        foreach( $this->contentDimensions as $dimensionName => $dimensionConfiguration) {

            $result[$dimensionName] = [
                'label' => $dimensionConfiguration['label'],
                'icon' => $dimensionConfiguration['icon'],
                'presets' => array_map(
                    function($preset) {
                        return $preset['label'];
                    },
                    array_filter($dimensionConfiguration['presets'])
                )
            ];

        }
        return $result;
    }

    protected function getContentDimensionValues ($dimensionName, $presetName) {
        return $this->contentDimensions[$dimensionName]['presets'][$presetName]['values'];
    }

    /**
     *
     */
    public function newVocabularyAction($dimensions = [])
    {
    }

    /**
     * @param string $title
     * @param string $description
     */
    public function createVocabularyAction($title, $description = '')
    {
        $context = $this->contextFactory->create();
        $taxonomyRoot = $this->taxonomyService->getRoot($context);

        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($this->taxonomyService->getVocabularyNodeType()));
        $nodeTemplate->setName(CrUtitlity::renderValidNodeName($title));
        $nodeTemplate->setProperty('title', $title);
        $nodeTemplate->setProperty('description', $description);

        $vocabulary = $taxonomyRoot->createNodeFromTemplate($nodeTemplate);

        $this->flashMessageContainer->addMessage(new Message(sprintf('Created vocabulary %s at path %s' , $title, $vocabulary->getPath())));
        $this->redirect('index');
    }

    /**
     * @param NodeInterface $vocabulary
     */
    public function vocabularyAction(NodeInterface $vocabulary) {
        $flowQuery = new FlowQuery([$vocabulary]);
        $root = $flowQuery->closest('[instanceof ' . $this->taxonomyService->getRootNodeType() . ']')->get(0);

        $this->view->assign('taxonomyRoot', $root);
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
     * @param NodeInterface $taxonomy
     */
    public function taxonomyAction(NodeInterface $taxonomy) {
        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')->get(0);

        $this->view->assign('taxonomy', $taxonomy);
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
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($this->taxonomyService->getTaxonomyNodeType()));
        $nodeTemplate->setName(CrUtitlity::renderValidNodeName($title));
        $nodeTemplate->setProperty('title', $title);
        $nodeTemplate->setProperty('description', $description);

        $taxonomy = $parent->createNodeFromTemplate($nodeTemplate);

        $this->flashMessageContainer->addMessage(new Message(sprintf('Created taxonomy %s at path %s', $title, $taxonomy->getPath() )));

        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')->get(0);

        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary->getContextPath()]);
    }

    /**
     * @param NodeInterface $taxonomy
     */
    public function editTaxonomyAction(NodeInterface $taxonomy)
    {
        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')->get(0);

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
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')->get(0);

        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary->getContextPath()]);
    }

    /**
     * @param NodeInterface $taxonomy
     */
    public function deleteTaxonomyAction(NodeInterface $taxonomy)
    {
        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')->get(0);

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
