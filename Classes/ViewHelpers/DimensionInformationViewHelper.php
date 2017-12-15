<?php
namespace Sitegeist\Taxonomy\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class DimensionInformationViewHelper extends AbstractViewHelper
{
    /**
     * @param NodeInterface $node
     * @param string $dimension dimension name
     * @return string value with replaced text
     * @api
     */
    public function render(NodeInterface $node, $dimension = null)
    {
        return self::renderStatic(['node' => $node, 'dimension' =>  $dimension], $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @throws InvalidVariableException
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /**
         * @var NodeInterface $node
         */
        $node = $arguments['node'];
        $dimension = $arguments['dimension'];
        if ($dimension) {
            return $node->getContext()->getTargetDimensions()[$dimension];
        } else {
            return json_encode($node->getContext()->getTargetDimensions());
        }
    }
}
