<?php
namespace Sitegeist\Taxonomy\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class PathInformationViewHelper extends AbstractViewHelper
{
    /**
     * @param NodeInterface $node
     * @return string value with replaced text
     * @api
     */
    public function render(NodeInterface $node)
    {
        return self::renderStatic(['node' => $node], $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @throws InvalidVariableException
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        /**
         * @var NodeInterface $node
         */
        $node = $arguments['node'];
        return implode(' -> ', array_slice(explode('/', $node->getPath()), 3));
    }
}
