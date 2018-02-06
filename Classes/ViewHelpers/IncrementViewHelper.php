<?php
namespace Sitegeist\Taxonomy\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class IncrementViewHelper extends AbstractViewHelper
{
    /**
     * @param number $value
     * @return number incremented 
     * @api
     */
    public function render($value = 0)
    {
        return self::renderStatic(
            [
                'value' => $value
            ],
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
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
        $value = $arguments['value'];
        return $value + 1;
    }
}
