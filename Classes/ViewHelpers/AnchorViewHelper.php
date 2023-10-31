<?php

namespace B13\Menus\ViewHelpers;

use B13\Menus\Helpers\HelperFunctions;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

final class AnchorViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    protected $escapeOutput = false;

    public function initializeArguments()
    {
        // registerArgument($name, $type, $description, $required, $defaultValue, $escape)
        $this->registerArgument('title', 'string', 'The string which should be converted to be used as an html identifier', true);
    }

    public static function renderStatic(
        array                     $arguments,
        \Closure                  $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        $title = $renderChildrenClosure();
        return HelperFunctions::getAnchorId($title);
    }

}
