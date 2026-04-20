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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Page\PageInformation;

/**
 * DataProcessor to retrieve a list of all pages of the current rootline to build a breadcrumb menu.
 */
class BreadcrumbsMenu extends AbstractMenu
{
    public function __construct(ContentDataProcessor $contentDataProcessor, protected MenuRepository $menuRepository)
    {
        parent::__construct($contentDataProcessor);
    }

    /**
     * @inheritDoc
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }
        $rootLine = $this->getRootline($cObj);
        $pages = $this->menuRepository->getBreadcrumbsMenu($rootLine, $processorConfiguration);
        $rootLevelCount = count($pages);
        foreach ($pages as &$page) {
            PageStateMarker::markStates($page, $rootLevelCount--);
        }
        foreach ($pages as &$page) {
            $this->processAdditionalDataProcessors($page, $processorConfiguration, $cObj->getRequest());
        }
        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration, 'breadcrumbs');
        $processedData[$targetVariableName] = $pages;
        return $processedData;
    }

    protected function getRootline(ContentObjectRenderer $cObj): array
    {
        if ((new Typo3Version())->getMajorVersion() < 13) {
            return $GLOBALS['TSFE']->rootLine;
        }
        /** @var PageInformation $pageInformation */
        $pageInformation = $cObj->getRequest()->getAttribute('frontend.page.information');
        return $pageInformation->getRootLine();
    }
}
