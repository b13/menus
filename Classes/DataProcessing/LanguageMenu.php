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

use B13\Menus\Compiler\LanguageMenuCompiler;
use B13\Menus\PageStateMarker;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * DataProcessor to retrieve a list of a all available languages.
 */
class LanguageMenu implements DataProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        $pages = GeneralUtility::makeInstance(LanguageMenuCompiler::class)->compile($cObj, $processorConfiguration);
        $currentLanguage = $this->getCurrentSiteLanguage();
        foreach ($pages as &$page) {
            PageStateMarker::markStates($page);
            if ((int)$page['sys_language_uid'] === $currentLanguage->getLanguageId()) {
                $page['isActiveLanguage'] = true;
            }
        }
        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration);
        $processedData[$targetVariableName] = $pages;
        return $processedData;
    }

    protected function getCurrentSiteLanguage(): ?SiteLanguage
    {
        $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
    }
}
