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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class AnchorMenuCompiler extends AbstractMenuCompiler
{
    /**
     * @inheritDoc
     */
    public function compile(ContentObjectRenderer $contentObjectRenderer, array $configuration): array
    {

        $cacheIdentifier = $this->generateCacheIdentifierForMenu('anchor', $configuration);
        $excludePages = $this->parseStdWrap($configuration['excludePages'] ?? '', $configuration['excludePages.'] ?? []);
        $configuration['excludePages'] = $excludePages;
        $depth = (int)$contentObjectRenderer->stdWrap($configuration['depth'] ?? 1, $configuration['depth.'] ?? []);


        $pageIds = $contentObjectRenderer->stdWrap($configuration['pages'] ?? $this->getPageIds($this->menuRepository->getSubPagesOfPage($this->getCurrentSite()->getRootPageId(), $depth, $configuration)), $configuration['pages.'] ?? []);
        $pageIds = GeneralUtility::intExplode(',', (string)$pageIds);

        $cacheIdentifier .= '-' . substr(md5(json_encode([$pageIds])), 0, 10);

        return $this->cache->get($cacheIdentifier, function () use ($configuration, $pageIds) {
            $pages = [];
            foreach ($pageIds as $pageId) {
                $page = $this->menuRepository->getPage($pageId, $configuration);
                $links = $this->menuRepository->getAnchorMenu($pageId, $configuration);
                $page["anchors"] = $links;
                if (!empty($page)) {
                    $pages[$pageId] = $page;
                }
            }
            return $pages;
        });
    }

    /**
     * Get page ids from rootline + depth
     *
     * @param $pageArr
     * @return string
     */
    public function getPageIds($pageArr)
    {
        $pageIds = [];
        foreach ($pageArr as $page) {
            $pageIds[] = $page["uid"];
        }
        return implode(",", $pageIds);
    }
}
