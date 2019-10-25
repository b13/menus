<?php
declare(strict_types = 1);
namespace B13\Menus\DataProcessing;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Menus\Compiler\TreeMenuCompiler;
use B13\Menus\PageStateMarker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * DataProcessor to render a tree-based menu of pages and subpages.
 */
class TreeMenu implements DataProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        $pages = GeneralUtility::makeInstance(TreeMenuCompiler::class)->compile($cObj, $processorConfiguration);
        foreach ($pages as &$page) {
            PageStateMarker::markStatesRecursively($page, 1);
        }
        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration);
        $processedData[$targetVariableName] = $pages;
        return $processedData;
    }
}
