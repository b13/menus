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

use B13\Menus\Domain\Repository\MenuRepository;
use B13\Menus\PageStateMarker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * DataProcessor to retrieve a list of all pages of the current rootline to build a breadcrumb menu.
 */
class BreadcrumbsMenuDataProcessor implements DataProcessorInterface
{
    /**
     * @var MenuRepository
     */
    protected $menuRepository;

    public function __construct()
    {
        $this->menuRepository = GeneralUtility::makeInstance(MenuRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        $pages = $this->menuRepository->getBreadcrumbsMenu($GLOBALS['TSFE']->rootLine);
        $rootLevelCount = count($pages);
        foreach ($pages as $page) {
            PageStateMarker::markStates($page, $rootLevelCount--);
        }
        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration);
        $processedData[$targetVariableName] = $pages;
        return $processedData;
    }
}
