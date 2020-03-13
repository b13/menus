<?php
declare(strict_types = 1);
namespace B13\Menus\Compiler;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class BreadcrumbsCompiler extends AbstractMenuCompiler
{
    /**
     * @inheritDoc
     */
    public function compile(ContentObjectRenderer $contentObjectRenderer, array $configuration): array
    {
        $cacheIdentifier = $this->generateCacheIdentifierForMenu('list', $configuration);

        $excludePages = $contentObjectRenderer->stdWrap($configuration['excludePages'] ?? '', $configuration['excludePages.']);
        $excludePages = GeneralUtility::trimExplode(',', $excludePages);
        
        $cacheIdentifier .= '-' . GeneralUtility::shortMD5(json_encode([$excludePages]));

        return $this->cache->get($cacheIdentifier, function () use ($contentObjectRenderer, $configuration, $excludePages) {

            $pages = $this->menuRepository->getBreadcrumbsMenu($GLOBALS['TSFE']->rootLine, $configuration);
            return $pages;
        });
    }
}
