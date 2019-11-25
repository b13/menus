<?php
declare(strict_types = 1);
namespace B13\Menus;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This is a helper class and a wrapper around "cache_hash".
 *
 * The pure joy of this class is the get() method, which calculates tags and max lifetime based on the fetched
 * records. If found in cache, fetched directly.
 */
class CacheHelper implements SingletonInterface
{
    /**
     * @var FrontendInterface
     */
    protected $cache;

    public function __construct(FrontendInterface $cache = null)
    {
        $this->cache = $cache ?? GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_hash');
    }

    /**
     * Looks up the items inside the cache, if it exists, takes the cached entry, otherwise computes the data
     * via the $loader().
     *
     * @param string $cacheIdentifier
     * @param callable $loader
     * @return array
     */
    public function get(string $cacheIdentifier, callable $loader): array
    {
        $pages = $this->cache->get($cacheIdentifier);
        if (is_array($pages)) {
            return $pages;
        }

        // Do the actual work
        $pages = $loader();

        // Calculate tags + lifetime
        $usedPageIds = $this->getAllPageIdsFromItems($pages);
        $tags = array_map(function($pageId) {
            return 'pageId_' . $pageId;
        }, $usedPageIds);
        $maximumLifeTime = $this->getMaxLifetimeOfPages($pages, $GLOBALS['TSFE']->get_cache_timeout());
        $this->cache->set($cacheIdentifier, $pages, $tags, $maximumLifeTime);
        return $pages;
    }

    /**
     * Fetch all IDs of a tree recursively, in order to tag the cache entries properly.
     *
     * @param array $pages
     * @return int[] a flat array with only the IDs (as integer)
     */
    protected function getAllPageIdsFromItems(array $pages): array
    {
        $pageIds = [];
        foreach ($pages as $page) {
            $pageIds[] = (int)$page['uid'];
            if (!empty($page['subpages'])) {
                $pageIds = array_merge($pageIds, $this->getAllPageIdsFromItems($page['subpages']));
            }
        }
        return $pageIds;
    }

    /**
     * pages.cache_timeout is not used here, as this is supposed to be relevant for content of a page, not the
     * metadata.
     *
     * @param array $pages
     * @param null $maxLifetime
     * @return int|null
     */
    protected function getMaxLifetimeOfPages(array $pages, $maxLifetime = null): ?int
    {
        foreach ($pages as $page) {
            if (!empty($page['endtime'])) {
                $maxLifetimeOfPage = $page['endtime'] - $GLOBALS['EXEC_TIME'];
                if ($maxLifetimeOfPage < $maxLifetime) {
                    $maxLifetime = $maxLifetimeOfPage;
                }
            }
            if (!empty($page['subpages'])) {
                $maxLifetime = $this->getMaxLifetimeOfPages($page['subpages'], $maxLifetime);
            }
        }
        return $maxLifetime;
    }
}
