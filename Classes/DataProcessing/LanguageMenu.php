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

use B13\Menus\Compiler\LanguageMenuCompiler;
use B13\Menus\PageStateMarker;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * DataProcessor to retrieve a list of a all available languages.
 */
class LanguageMenu extends AbstractMenu
{
    /**
     * @inheritDoc
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }
        $pages = GeneralUtility::makeInstance(LanguageMenuCompiler::class)->compile($cObj, $processorConfiguration);
        $currentLanguage = $this->getCurrentSiteLanguage();
        foreach ($pages as &$page) {
            PageStateMarker::markStates($page);
            if ((int)$page['sys_language_uid'] === $currentLanguage->getLanguageId()) {
                $page['isActiveLanguage'] = true;
            }
        }
        foreach ($pages as &$page) {
            $this->processAdditionalDataProcessors($page, $processorConfiguration);
        }
        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration);
        $processedData[$targetVariableName] = $pages;
        return $processedData;
    }

    /**
     * @return SiteLanguage|null
     */
    protected function getCurrentSiteLanguage(): ?SiteLanguage
    {
        return $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
    }
}
