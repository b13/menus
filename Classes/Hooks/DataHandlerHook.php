<?php

declare(strict_types = 1);

namespace B13\Menus\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This is a helper class and a wrapper around "cache_hash".
 *
 * The pure joy of this class is the get() method, which calculates tags and max lifetime based on the fetched
 * records. If found in cache, fetched directly.
 */
class DataHandlerHook
{
    /**
     * @var FrontendInterface
     */
    protected $cacheHash;

    /**
     * @var FrontendInterface
     */
    protected $cachePages;

    public function __construct(
        FrontendInterface $cacheHash = null,
        FrontendInterface $cachePages = null
    ) {
        $this->cacheHash = $cacheHash ?? GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_hash');
        $this->cachePages = $cachePages ?? GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_pages');
    }

    public function clearMenuCaches(array $params, DataHandler $dataHandler): void
    {
        if ($params['table'] !== 'pages' || empty($params['tags'])) {
            return;
        }
        $menuTags = [];
        foreach ($params['tags'] as $tag => $_) {
            if (strpos($tag, 'pageId_') === 0) {
                $menuTags[] = str_replace('pageId_', 'menuId_', $tag);
            }
        }
        $this->cacheHash->flushByTags($menuTags);
        $this->cachePages->flushByTags($menuTags);
    }
}
