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

class TreeMenuCompiler extends AbstractMenuCompiler
{
    /**
     * @inheritDoc
     */
    public function compile(ContentObjectRenderer $contentObjectRenderer, array $configuration): array
    {
        $cacheIdentifier = $this->generateCacheIdentifierForMenu('tree', $configuration);

        $includeStartPageIds = $contentObjectRenderer->stdWrap($configuration['includeRootPages'] ?? false, $configuration['includeRootPages.'] ?? []);
        $startPageIds = $contentObjectRenderer->stdWrap($configuration['entryPoints'] ?? $this->getCurrentSite()->getRootPageId(), $configuration['entryPoints.'] ?? []);
        $startPageIds = GeneralUtility::intExplode(',', (string)$startPageIds);
        $depth = (int)$contentObjectRenderer->stdWrap($configuration['depth'] ?? 1, $configuration['depth.'] ?? []);
        $excludePages = $this->parseStdWrap($configuration['excludePages'] ?? '', $configuration['excludePages.'] ?? []);
        $configuration['excludePages'] = $excludePages;

        $cacheIdentifier .= '-' . substr(md5(json_encode([$includeStartPageIds, $startPageIds, $depth, $excludePages])), 0, 10);

        return $this->cache->get($cacheIdentifier, function () use ($configuration, $includeStartPageIds, $startPageIds, $depth) {
            $tree = [];
            foreach ($startPageIds as $startPageId) {
                if ($includeStartPageIds) {
                    $page = $this->menuRepository->getPageTree($startPageId, $depth, $configuration);
                    if (!empty($page)) {
                        $tree[] = $page;
                    }
                } else {
                    $pages = $this->menuRepository->getSubPagesOfPage($startPageId, $depth-1, $configuration);
                    if (!empty($pages)) {
                        $tree = array_merge($tree, $pages);
                    }
                }
            }
            return $tree;
        });
    }
}
