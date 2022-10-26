<?php

declare(strict_types=1);

namespace B13\Menus\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * This hook is triggered before caches for a page get flushed.
 *
 * Ideally this should not trigger the cache flush but only allow to add tags.
 */
class DataHandlerHook
{
    protected CacheManager $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function clearMenuCaches(array $params, DataHandler $dataHandler): void
    {
        $pageId = (int)($params['uid_page'] ?? 0);
        if (($params['table'] ?? '') !== 'pages' || $pageId === 0) {
            return;
        }

        $menuTags = ['menuId_' . $pageId];
        // Clear caches of the parent page as well (needed when moving records)
        $parentPageId = $dataHandler->getPID('pages', $pageId);
        if ($parentPageId > 0) {
            $menuTags[] = 'menuId_' . $parentPageId;
        }
        $this->cacheManager->flushCachesByTags($menuTags);
    }
}
