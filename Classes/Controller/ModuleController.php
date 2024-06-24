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
use Neos\Fusion\View\FusionView;
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
     * @var string
     */
    protected $defaultViewObjectName = FusionView::class;

    /**
     * @var FusionView
     */
    protected $view;

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
     * @var array
     * @Flow\InjectConfiguration(path="backendModule.additionalFusionIncludePathes")
     */
    protected $additionalFusionIncludePathes;

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
     * @var NodeInterface
     */
    protected $defaultRoot;

    /**
     * Initialize the view
     *
     * @param  ViewInterface $view
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
        $fusionPathes = ['resource://Sitegeist.Taxonomy/Private/Fusion/Backend'];
        if ($this->additionalFusionIncludePathes && is_array($this->additionalFusionIncludePathes)) {
            $fusionPathes = Arrays::arrayMergeRecursiveOverrule($fusionPathes, $this->additionalFusionIncludePathes);
        }
        $this->view->setFusionPathPatterns($fusionPathes);
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
        $vocabularyNodes = $flowQuery->children('[instanceof Sitegeist.Taxonomy:Vocabulary]')->get();

        // fetch name and base node of vocabulary
        $vocabularies = [];
        foreach ($vocabularyNodes as $vocabulary) {
            $vocabularies[] = [
                'node' => $vocabulary,
                'defaultNode' => $this->getNodeInDefaultDimensions($vocabulary)
            ];
        }
        usort($vocabularies, function (array $vocabularyA, array $vocabularyB) {
            return strcmp(
                $vocabularyA['node']->getProperty('title') ?: '',
                $vocabularyB['node']->getProperty('title') ?: ''
            );
        });

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
     * @param NodeInterface $node
     * @return NodeInterface|null
     */
    protected function getNodeInDefaultDimensions(NodeInterface $node) : ?NodeInterface
    {
        if (!$this->defaultRoot) {
            $this->defaultRoot = $this->taxonomyService->getRoot();
        }

        $flowQuery = new FlowQuery([$this->defaultRoot]);
        $defaultNode = $flowQuery->find('#' . $node->getIdentifier())->get(0);
        if ($defaultNode && $defaultNode !== $node) {
            return $defaultNode;
        } else {
            return null;
        }
    }

    /**
     * @param NodeInterface $node
     * @param array<NodeInterface> $parents
     * @return array
     */
    public function fetchChildTaxonomies(NodeInterface $node, array $parents = []) : array
    {
        $flowQuery = new FlowQuery([$node]);
        $childTaxonomies = $flowQuery->children('[instanceof ' . $this->taxonomyService->getTaxonomyNodeType() . ']')->get();
        $result = [];
        foreach ($childTaxonomies as $childTaxonomy) {
            $result[] = [
                'node' => $childTaxonomy,
                'defaultNode' => $this->getNodeInDefaultDimensions($childTaxonomy),
                'children' => $this->fetchChildTaxonomies($childTaxonomy, array_merge($parents, [$childTaxonomy])),
                'parents' => $parents
            ];
        }
        return $result;
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
        $this->view->assign('defaultVocabulary', $this->getNodeInDefaultDimensions($vocabulary));
        $taxonomies = $this->fetchChildTaxonomies($vocabulary);
        $this->view->assign('taxonomies', $taxonomies);
    }

    /**
     * Display a form that allows to create a new vocabulary
     *
     * @param NodeInterface $taxonomyRoot
     * @return void
     */
    public function newVocabularyAction(NodeInterface $taxonomyRoot)
    {
        $this->view->assign('taxonomyRoot', $taxonomyRoot);

    }

    /**
     * Create a new vocabulary
     *
     * @param NodeInterface $taxonomyRoot
     * @param array $properties
     * @return void
     */
    public function createVocabularyAction(NodeInterface $taxonomyRoot, array $properties)
    {
        $vocabularyNodeType = $this->nodeTypeManager->getNodeType($this->taxonomyService->getVocabularyNodeType());
        $vocabularyProperties = $vocabularyNodeType->getProperties();

        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($vocabularyNodeType);
        $nodeTemplate->setName(CrUtitlity::renderValidNodeName($properties['title']));
        foreach($properties as $name => $value) {
            if (array_key_exists($name, $vocabularyProperties)) {
                $nodeTemplate->setProperty($name, $value);
            }
        }

        $vocabulary = $taxonomyRoot->createNodeFromTemplate($nodeTemplate);

        $this->addFlashMessage(
            sprintf('Created vocabulary %s at path %s', $properties['title'], $vocabulary->getLabel())
        );
        $this->redirect('index', null, null, ['root' => $taxonomyRoot]);
    }

    /**
     * Show a form that allows to modify the given vocabulary
     *
     * @param NodeInterface $vocabulary
     * @return void
     */
    public function editVocabularyAction(NodeInterface $vocabulary)
    {
        $taxonomyRoot = $this->taxonomyService->getRoot($vocabulary->getContext());
        $this->view->assign('taxonomyRoot', $taxonomyRoot);
        $this->view->assign('vocabulary', $vocabulary);
        $this->view->assign('defaultVocabulary', $this->getNodeInDefaultDimensions($vocabulary));
    }

    /**
     * Apply changes to the given vocabulary
     *
     * @param NodeInterface $vocabulary
     * @param array $properties
     * @return void
     */
    public function updateVocabularyAction(NodeInterface $vocabulary, array $properties)
    {
        $taxonomyRoot = $this->taxonomyService->getRoot($vocabulary->getContext());
        $vocabularyProperties = $vocabulary->getNodeType()->getProperties();
        foreach($properties as $name => $value) {
            if (array_key_exists($name, $vocabularyProperties)) {
                $previous = $vocabulary->getProperty($name);
                if ($previous !== $value) {
                    $vocabulary->setProperty($name, $value);
                }
            }
        }

        $this->addFlashMessage(
            sprintf('Updated vocabulary %s', $vocabulary->getLabel())
        );
        $this->redirect('index', null, null, ['root' => $taxonomyRoot]);
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
            $this->addFlashMessage(
                sprintf('Deleted vocabulary %s', $path)
            );
        }
        $taxonomyRoot = $this->taxonomyService->getRoot($vocabulary->getContext());
        $this->redirect('index', null, null, ['root' => $taxonomyRoot]);
    }

    /**
     * Show a form to create a new taxonomy
     *
     * @param NodeInterface $parent
     * @return void
     */
    public function newTaxonomyAction(NodeInterface $parent)
    {
        $flowQuery = new FlowQuery([$parent]);
        $vocabulary = $flowQuery->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')->get(0);
        $this->view->assign('vocabulary', $vocabulary);
        $this->view->assign('parent', $parent);
    }

    /**
     * Create a new taxonomy
     *
     * @param NodeInterface $parent
     * @param array $properties
     * @return void
     */
    public function createTaxonomyAction(NodeInterface $parent, array $properties)
    {
        $taxonomyNodeType = $this->nodeTypeManager->getNodeType($this->taxonomyService->getTaxonomyNodeType());
        $taxomonyProperties = $taxonomyNodeType->getProperties();

        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($taxonomyNodeType);
        $nodeTemplate->setName(CrUtitlity::renderValidNodeName($properties['title']));

        foreach($properties as $name => $value) {
            if (array_key_exists($name, $taxomonyProperties)) {
                $nodeTemplate->setProperty($name, $value);
            }
        }

        $taxonomy = $parent->createNodeFromTemplate($nodeTemplate);

        $this->addFlashMessage(
            sprintf('Created taxonomy %s at path %s', $taxonomy->getLabel(), $taxonomy->getPath())
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
        $this->view->assign('defaultVocabulary', $this->getNodeInDefaultDimensions($vocabulary));

        $this->view->assign('taxonomy', $taxonomy);
        $this->view->assign('defaultTaxonomy', $this->getNodeInDefaultDimensions($taxonomy));
    }

    /**
     * Apply changes to the given taxonomy
     *
     * @param NodeInterface $taxonomy
     * @param array $properties
     * @return void
     */
    public function updateTaxonomyAction(NodeInterface $taxonomy, array $properties)
    {
        $taxonomyProperties = $taxonomy->getNodeType()->getProperties();
        foreach($properties as $name => $value) {
            if (array_key_exists($name, $taxonomyProperties)) {
                $previous = $taxonomy->getProperty($name);
                if ($previous !== $value) {
                    $taxonomy->setProperty($name, $value);
                }
            }
        }

        $this->addFlashMessage(
            sprintf('Updated taxonomy %s', $taxonomy->getPath())
        );

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
            throw new \Exception('cannot delete autocreated taxonomies');
        }

        $flowQuery = new FlowQuery([$taxonomy]);
        $vocabulary = $flowQuery
            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
            ->get(0);

        $taxonomy->remove();

        $this->addFlashMessage(
            sprintf('Deleted taxonomy %s', $taxonomy->getPath())
        );

        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary]);
    }

    /**
     * Move taxonomy up
     *
     * @param NodeInterface $taxonomy
     * @return void
     */
    public function moveUpTaxonomyAction(NodeInterface $taxonomy)
    {
        $this->persistenceManager->allowObject($taxonomy);

        $flowQuery = new FlowQuery([$taxonomy]);
        $nextSiblingNode = $flowQuery->prev()->get(0);
        $taxonomy->moveBefore($nextSiblingNode);
        $this->persistenceManager->persistAll();

        foreach ($taxonomy->getOtherNodeVariants() as $otherNodeVariant) {
            $otherNodeVariant->setIndex($taxonomy->getIndex());
        }
        $this->persistenceManager->persistAll();

        $this->addFlashMessage(
            sprintf('Moved up taxonomy %s', $taxonomy->getPath())
        );

        $vocabulary = $flowQuery
            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
            ->get(0);

        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary]);
    }

    /**
     * Move taxonomy down
     *
     * @param NodeInterface $taxonomy
     * @return void
     */
    public function moveDownTaxonomyAction(NodeInterface $taxonomy)
    {
        $this->persistenceManager->allowObject($taxonomy);

        $flowQuery = new FlowQuery([$taxonomy]);
        $nextSiblingNode = $flowQuery->next()->get(0);
        $taxonomy->moveAfter($nextSiblingNode);
        $this->persistenceManager->persistAll();

        foreach ($taxonomy->getOtherNodeVariants() as $otherNodeVariant) {
            $otherNodeVariant->setIndex($taxonomy->getIndex());
        }
        $this->persistenceManager->persistAll();

        $this->addFlashMessage(
            sprintf('Moved down taxonomy %s', $taxonomy->getPath())
        );

        $vocabulary = $flowQuery
            ->closest('[instanceof ' . $this->taxonomyService->getVocabularyNodeType() . ']')
            ->get(0);

        $this->redirect('vocabulary', null, null, ['vocabulary' => $vocabulary]);
    }

}
