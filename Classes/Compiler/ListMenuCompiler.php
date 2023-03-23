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

class ListMenuCompiler extends AbstractMenuCompiler
{
    /**
     * @inheritDoc
     */
    public function compile(ContentObjectRenderer $contentObjectRenderer, array $configuration): array
    {
        $cacheIdentifier = $this->generateCacheIdentifierForMenu('list', $configuration);

        $pageIds = $contentObjectRenderer->stdWrap($configuration['pages'] ?? $this->getCurrentSite()->getRootPageId(), $configuration['pages.'] ?? []);
        $pageIds = GeneralUtility::intExplode(',', (string)$pageIds);

        $cacheIdentifier .= '-' . substr(md5(json_encode([$pageIds])), 0, 10);

        return $this->cache->get($cacheIdentifier, function () use ($configuration, $pageIds) {
            $pages = [];
            foreach ($pageIds as $pageId) {
                $page = $this->menuRepository->getPage($pageId, $configuration);
                if (!empty($page)) {
                    $pages[] = $page;
                }
            }
            return $pages;
        });
    }
}
