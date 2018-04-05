<?php
namespace Sitegeist\Taxonomy\Controller;

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
use Neos\ContentRepository\Domain\Service\NodeServiceInterface;
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
     * @var NodeServiceInterface
     */
    protected $nodeService;

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
     * Show an overview of available vocabularies
     *
     * @param NodeInterface $root
     * @return void
     */
    public function indexAction(NodeInterface $root = null)
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
     * Switch to a modified content context and redirect to the given action
     *
     * @param string $targetAction the target action to redirect to
     * @param string $targetProperty the property in the target action that will accept the node
     * @param NodeInterface $contextNode the node to adjust the context for
     * @param array $dimensions array with dimensionName, presetName combinations
     * @return void
     */
    public function changeContextAction($targetAction, $targetProperty, NodeInterface $contextNode, $dimensions = [])
    {
        $contextProperties = $contextNode->getContext()->getProperties();

        $newContextProperties = [];
        foreach ($dimensions as $dimensionName => $presetName) {
            $newContextProperties['dimensions'][$dimensionName] = $this->getContentDimensionValues(
                $dimensionName,
                $presetName
            );
            $newContextProperties['targetDimensions'][$dimensionName] = $presetName;
        }
        $modifiedContext = $this->contextFactory->create(array_merge($contextProperties, $newContextProperties));
        $nodeInModifiedContext = $modifiedContext->getNodeByIdentifier($contextNode->getIdentifier());

        $this->redirect($targetAction, null, null, [$targetProperty => $nodeInModifiedContext]);
    }

    /**
     * Prepare all available content dimensions for use in a select box
     *
     * @return array the list of available content dimensions and their presets
     */
    protected function getContentDimensionOptions()
    {
        $result = [];

        if (is_array($this->contentDimensions) === false || count($this->contentDimensions) === 0) {
            return $result;
        }

        foreach ($this->contentDimensions as $dimensionName => $dimensionConfiguration) {
            $dimensionOption = [];
            $dimensionOption['label'] = array_key_exists('label', $dimensionConfiguration) ?
                $dimensionConfiguration['label'] : $dimensionName;
            $dimensionOption['presets'] = [];

            foreach ($dimensionConfiguration['presets'] as $presetKey => $presetConfiguration) {
                $dimensionOption['presets'][$presetKey] = array_key_exists('label', $presetConfiguration) ?
                    $presetConfiguration['label'] : $presetKey;
            }

            $result[$dimensionName] = $dimensionOption;
        }

        return $result;
    }

    /**
     * Get the content dimension values for a given content dimension and preset
     *
     * @param $dimensionName
     * @param $presetName
     * @return array the values assiged to the preset identified by $dimensionName and $presetName
     */
    protected function getContentDimensionValues($dimensionName, $presetName)
    {
        return $this->contentDimensions[$dimensionName]['presets'][$presetName]['values'];
    }

    /**
     * Show the given vocabulary
     *
     * @param NodeInterface $vocabulary
     * @return void
     */
    public function vocabularyAction(NodeInterface $vocabulary)
    {
        $flowQuery = new FlowQuery([$vocabulary]);
        $root = $flowQuery->closest('[instanceof ' . $this->taxonomyService->getRootNodeType() . ']')->get(0);

        $this->view->assign('taxonomyRoot', $root);
        $this->view->assign('vocabulary', $vocabulary);
    }

    /**
     * Display a form that allows to create a new vocabulary
     *
     * @return void
     */
    public function newVocabularyAction()
    {
    }

    /**
     * Create a new vocabulary
     *
     * @param string $title
     * @param string $description
     * @return void
     */
    public function createVocabularyAction($title, $description = '')
    {
        $context = $this->contextFactory->create();
        $taxonomyRoot = $this->taxonomyService->getRoot($context);
        $vocabularyNodeType = $this->nodeTypeManager->getNodeType($this->taxonomyService->getVocabularyNodeType());

        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($vocabularyNodeType);
        $nodeTemplate->setName(CrUtitlity::renderValidNodeName($title));
        $nodeTemplate->setProperty('title', $title);
        $nodeTemplate->setProperty('description', $description);

        $vocabulary = $taxonomyRoot->createNodeFromTemplate($nodeTemplate);

        $this->flashMessageContainer->addMessage(
            new Message(sprintf('Created vocabulary %s at path %s', $title, $vocabulary->getPath()))
        );
        $this->redirect('index');
    }

    /**
     * Show a form that allows to modify the given vocabulary
     *
     * @param NodeInterface $vocabulary
     * @return void
     */
    public function editVocabularyAction(NodeInterface $vocabulary)
    {
        $this->view->assign('vocabulary', $vocabulary);
    }

    /**
     * Apply changes to the given vocabulary
     *
     * @param NodeInterface $vocabulary
     * @param string $title
     * @param string $description
     * @return void
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
                    $newName = $this->nodeService->generateUniqueNodeName($vocabulary->getParentPath(), $possibleName);
                    $vocabulary->setName($newName);
                }
            }
        }

        if ($previousDescription !== $description) {
            $vocabulary->setProperty('description', $description);
        }

        $this->flashMessageContainer->addMessage(new Message(sprintf('Updated vocabulary %s', $title)));
        $this->redirect('index');
    }

    /**
     * Delete the given vocabulary
     *
     * @param NodeInterface $vocabulary
     * @return void
     * @throws \Exception
     */
    public function deleteVocabularyAction(NodeInterface $vocabulary)
    {
        if ($vocabulary->isAutoCreated()) {
            throw new \Exception('cannot delete autocrated vocabularies');
        } else {
            $path = $vocabulary->getPath();
            $vocabulary->remove();
            $this->flashMessageContainer->addMessage(new Message(sprintf('Deleted vocabulary %s', $path)));
        }
        $this->redirect('index');
    }

    /**
     * Show the given taxonomy
     *
     * @param NodeInterface $taxonomy
     * @return void
     */
    public function taxonomyAction(NodeInterface $taxonomy)
    {
        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery
            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
            ->get(0);

        $this->view->assign('taxonomy', $taxonomy);
        $this->view->assign('vocabulary', $vocabulary);
    }

    /**
     * Show a form to create a new taxonomy
     *
     * @param NodeInterface $parent
     * @return void
     */
    public function newTaxonomyAction(NodeInterface $parent)
    {
        $this->view->assign('parent', $parent);
        $this->view->assign('nodeTypeName', $this->nodeTypeManager->getNodeType($this->taxonomyService->getTaxonomyNodeType($parent)));
    }

    /**
     * Create a new taxonomy
     *
     * @param NodeInterface $parent
     * @param string $title
     * @param string $description
     * @param array $additionalData
     * @return void
     */
    public function createTaxonomyAction(NodeInterface $parent, $title, $description = '', array $additionalData = null)
    {
        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType($this->taxonomyService->getTaxonomyNodeType($parent)));
        $nodeTemplate->setName(CrUtitlity::renderValidNodeName($title));
        $nodeTemplate->setProperty('title', $title);
        $nodeTemplate->setProperty('description', $description);

        if ($additionalData !== null) {
            foreach ($additionalData as $key => $value) {
                $nodeTemplate->setProperty($key, $value);
            }
        }

        $taxonomy = $parent->createNodeFromTemplate($nodeTemplate);

        $this->flashMessageContainer->addMessage(
            new Message(sprintf('Created taxonomy %s at path %s', $title, $taxonomy->getPath()))
        );

        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery
            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
            ->get(0);

        $this->redirect(
            'vocabulary',
            null,
            null,
            ['vocabulary' => $vocabulary->getContextPath()]
        );
    }

    /**
     * Display a form that allows to modify the given taxonomy
     *
     * @param NodeInterface $taxonomy
     * @return void
     */
    public function editTaxonomyAction(NodeInterface $taxonomy)
    {
        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery
            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
            ->get(0);

        $this->view->assign('vocabulary', $vocabulary);
        $this->view->assign('taxonomy', $taxonomy);
        $this->view->assign('nodeTypeName', $this->nodeTypeManager->getNodeType($this->taxonomyService->getTaxonomyNodeType($taxonomy)));
    }

    /**
     * Apply changes to the given taxonomy
     *
     * @param NodeInterface $taxonomy
     * @param string $title
     * @param string $description
     * @param array $additionalData
     * @return void
     */
    public function updateTaxonomyAction(NodeInterface $taxonomy, $title, $description = '', array $additionalData = null)
    {
        $previousTitle = $taxonomy->getProperty('title');
        $previousDescription = $taxonomy->getProperty('description');

        if ($previousTitle !== $title) {
            $taxonomy->setProperty('title', $title);
            if ($taxonomy->isAutoCreated() === false) {
                $possibleName = CrUtitlity::renderValidNodeName($title);
                if ($taxonomy->getName() !== $possibleName) {
                    $newName = $this->nodeService->generateUniqueNodeName($taxonomy->getParentPath(), $possibleName);
                    $taxonomy->setName($newName);
                }
            }
        }

        if ($previousDescription !== $description) {
            $taxonomy->setProperty('description', $description);
        }

        if ($additionalData !== null) {
            foreach ($additionalData as $key => $value) {
                $taxonomy->setProperty($key, $value);
            }
        }

        $this->flashMessageContainer->addMessage(new Message(sprintf('Updated taxonomy %s', $taxonomy->getPath())));

        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery
            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
            ->get(0);

        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary->getContextPath()]);
    }

    /**
     * Delete the given taxonomy
     *
     * @param NodeInterface $taxonomy
     * @return void
     */
    public function deleteTaxonomyAction(NodeInterface $taxonomy)
    {
        if ($taxonomy->isAutoCreated()) {
            throw new \Exception('cannot delete autocrated taxonomies');
        }

        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery
            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
            ->get(0);

        $taxonomy->remove();

        $this->flashMessageContainer->addMessage(new Message(sprintf('Deleted taxonomy %s', $taxonomy->getPath())));
        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary]);
    }
}
