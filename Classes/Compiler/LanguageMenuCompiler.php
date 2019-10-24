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

use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class LanguageMenuCompiler extends AbstractMenuCompiler
{
    /**
     * @inheritDoc
     */
    public function compile(ContentObjectRenderer $contentObjectRenderer, array $configuration): array
    {
        $cacheIdentifier = $this->generateCacheIdentifierForMenu('list', $configuration);
        return $this->cache->get($cacheIdentifier, function() use ($contentObjectRenderer, $configuration) {
            $excludedLanguages = $contentObjectRenderer->stdWrap($configuration['excludeLanguages'] ?? '', $configuration['excludeLanguages.']);
            $excludedLanguages = GeneralUtility::trimExplode(',', $excludedLanguages);
            $targetPage = $contentObjectRenderer->stdWrap($configuration['pointToPage'] ?? $GLOBALS['TSFE']->id, $configuration['pointToPage.']);
            $targetPage = (int)$targetPage;

            $site = $this->getCurrentSite();
            $pages = [];
            foreach ($site->getLanguages() as $language) {
                if (in_array($language->getTwoLetterIsoCode(), $excludedLanguages, true)) {
                    continue;
                }
                if (in_array($language->getLanguageId(), $excludedLanguages, true)) {
                    continue;
                }
                $languageAspect = LanguageAspectFactory::createFromSiteLanguage($language);
                $page = $this->menuRepository->getPageInLanguage($targetPage, $languageAspect);
                if (!empty($page)) {
                    // @todo: we need to have this prefixed with "language_"
                    $page = array_merge($page, $language->toArray());
                    $pages[] = $page;
                }
            }
        });
    }
}
