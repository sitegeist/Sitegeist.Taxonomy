<?php
namespace Sitegeist\Taxonomy\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class ReplaceViewHelper extends AbstractViewHelper
{
    /**
     * @param string $search
     * @param string $replace
     * @param string $value
     * @return string value with replaced text
     * @api
     */
    public function render($search, $replace, $value = null)
    {
        return self::renderStatic(['value' => $value, 'search' => $search, 'replace' => $replace], $this->buildRenderChildrenClosure(), $this->renderingContext);
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
        $search = $arguments['search'];
        $replace = $arguments['replace'];
        $value = $arguments['value'];
        if ($value === null) {
            $value = $renderChildrenClosure();
        }
        return str_replace($search, $replace, $value);
    }
}
