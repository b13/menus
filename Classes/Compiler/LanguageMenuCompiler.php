<?php

declare(strict_types=1);
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

class LanguageMenuCompiler extends AbstractMenuCompiler
{
    /**
     * @inheritDoc
     */
    public function compile(ContentObjectRenderer $contentObjectRenderer, array $configuration): array
    {
        $cacheIdentifier = $this->generateCacheIdentifierForMenu('list', $configuration);

        $excludedLanguages = $contentObjectRenderer->stdWrap($configuration['excludeLanguages'] ?? '', $configuration['excludeLanguages.'] ?? []);
        $excludedLanguages = GeneralUtility::trimExplode(',', $excludedLanguages, true);
        $targetPage = $contentObjectRenderer->stdWrap($configuration['pointToPage'] ?? $GLOBALS['TSFE']->id, $configuration['pointToPage.'] ?? []);
        $targetPage = (int)$targetPage;
        $addAllSiteLanguages = isset($configuration['addAllSiteLanguages']) && (bool)$configuration['addAllSiteLanguages'] === true;

        $cacheIdentifier .= '-' . substr(md5(json_encode([$excludedLanguages, $targetPage])), 0, 10);

        return $this->cache->get($cacheIdentifier, function () use ($configuration, $excludedLanguages, $targetPage, $addAllSiteLanguages) {
            $site = $this->getCurrentSite();
            $context = clone GeneralUtility::makeInstance(Context::class);
            $pages = [];
            foreach ($site->getLanguages() as $language) {
                if (in_array($language->getTwoLetterIsoCode(), $excludedLanguages, true)) {
                    continue;
                }
                if (in_array((string)$language->getLanguageId(), $excludedLanguages, true)) {
                    continue;
                }
                $languageAspect = LanguageAspectFactory::createFromSiteLanguage($language);
                $context->setAspect('language', $languageAspect);
                $page = $this->menuRepository->getPageInLanguage($targetPage, $context, $configuration);
                if (!empty($page)) {
                    $page['language'] = $language->toArray();
                    $page['pageIsAvailable'] = true;
                    $pages[] = $page;
                } elseif ($addAllSiteLanguages === true) {
                    $page = [
                        'language' => $language->toArray(),
                        'pageIsAvailable' => false,
                    ];
                    $pages[] = $page;
                }
            }
            return $pages;
        });
    }
}
