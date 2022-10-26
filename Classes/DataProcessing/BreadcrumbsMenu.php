<?php

declare(strict_types=1);
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
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * DataProcessor to retrieve a list of all pages of the current rootline to build a breadcrumb menu.
 */
class BreadcrumbsMenu extends AbstractMenu
{
    protected MenuRepository $menuRepository;

    public function __construct(ContentDataProcessor $contentDataProcessor, MenuRepository $menuRepository)
    {
        parent::__construct($contentDataProcessor);
        $this->menuRepository = $menuRepository;
    }

    /**
     * @inheritDoc
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }
        $pages = $this->menuRepository->getBreadcrumbsMenu($GLOBALS['TSFE']->rootLine, $processorConfiguration);
        $rootLevelCount = count($pages);
        foreach ($pages as &$page) {
            PageStateMarker::markStates($page, $rootLevelCount--);
        }
        foreach ($pages as &$page) {
            $this->processAdditionalDataProcessors($page, $processorConfiguration);
        }
        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration, 'breadcrumbs');
        $processedData[$targetVariableName] = $pages;
        return $processedData;
    }
}
