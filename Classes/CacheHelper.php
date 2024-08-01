<?php

declare(strict_types=1);
namespace B13\Menus;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Cache\CacheLifetimeCalculator;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This is a helper class and a wrapper around "cache_hash".
 *
 * The pure joy of this class is the get() method, which calculates tags and max lifetime based on the fetched
 * records. If found in cache, fetched directly.
 */
class CacheHelper implements SingletonInterface
{
    protected FrontendInterface $cache;
    protected bool $disableCaching = false;
    protected Context $context;

    public function __construct(FrontendInterface $cache, Context $context)
    {
        $this->context = $context;
        $this->cache = $cache;
        try {
            $this->disableCaching = $context->getPropertyFromAspect('workspace', 'id', 0) > 0;
        } catch (AspectNotFoundException $e) {
        }
        if ($this->disableCaching === false) {
            try {
                $this->disableCaching = $context->getPropertyFromAspect('frontend.preview', 'isPreview', false);
            } catch (AspectNotFoundException $e) {
            }
        }
    }

    /**
     * Looks up the items inside the cache, if it exists, takes the cached entry, otherwise computes the data
     * via the $loader().
     */
    public function get(string $cacheIdentifier, callable $loader): array
    {
        if ($this->disableCaching) {
            return $loader();
        }
        $pages = $this->cache->get($cacheIdentifier);
        if (is_array($pages)) {
            $this->buildTagsAndAddThemToPageCache($pages);
            return $pages;
        }

        // Do the actual work
        $pages = $loader();

        // Calculate tags + lifetime
        $tags = $this->buildTagsAndAddThemToPageCache($pages);
        $defaultMaxLifeTime = $this->getDefaultMaxLifeTime();
        $maximumLifeTime = $this->getMaxLifetimeOfPages($pages, $defaultMaxLifeTime);
        $this->cache->set($cacheIdentifier, $pages, $tags, $maximumLifeTime);
        return $pages;
    }

    /**
     * @param mixed[] $pages
     * @return string[]
     */
    protected function buildTagsAndAddThemToPageCache(array $pages): array
    {
        $usedPageIds = array_unique($this->getAllPageIdsFromItems($pages));
        $tags = array_map(function ($pageId) {
            return 'menuId_' . $pageId;
        }, $usedPageIds);
        $this->getFrontendController()->addCacheTags($tags);
        return $tags;
    }

    /**
     * Fetch all IDs of a tree recursively, in order to tag the cache entries properly.
     *
     * Only pages which have subpages are included, as the "leave pages" are detected on cache flush.
     * This reduces the amount of tags in the cache.
     *
     * @param array $pages
     * @return int[] a flat array with only the IDs (as integer)
     */
    protected function getAllPageIdsFromItems(array $pages): array
    {
        $pageIds = [];
        foreach ($pages as $page) {
            if (!isset($page['uid'])) {
                continue;
            }
            if (!empty($page['subpages'])) {
                $pageIds[] = (int)$page['uid'];
                $pageIds = array_merge($pageIds, $this->getAllPageIdsFromItems($page['subpages']));
            } else {
                $pageIds[] = (int)$page['pid'];
            }
        }
        return $pageIds;
    }

    protected function getDefaultMaxLifeTime(): int
    {
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $maxLifetime = (int)$this->getFrontendController()->get_cache_timeout();
        } else {
            $request = $this->getServerRequest();
            $pageInformation = $request->getAttribute('frontend.page.information');
            /** @var ?FrontendTypoScript $typoScript */
            $typoScript = $request->getAttribute('frontend.typoscript');
            if ($typoScript === null || $pageInformation === null) {
                return 0;
            }
            $typoScriptConfigArray = $typoScript->getConfigArray();
            $maxLifetime = GeneralUtility::makeInstance(CacheLifetimeCalculator::class)
                ->calculateLifetimeForPage(
                    $pageInformation->getId(),
                    $pageInformation->getPageRecord(),
                    $typoScriptConfigArray,
                    0,
                    $this->context
                );
        }
        return $maxLifetime;
    }

    /**
     * pages.cache_timeout is not used here, as this is supposed to be relevant for content of a page, not the
     * metadata.
     */
    protected function getMaxLifetimeOfPages(array $pages, int $maxLifetime): int
    {
        foreach ($pages as $page) {
            if (!empty($page['endtime'])) {
                $maxLifetimeOfPage = (int)$page['endtime'] - $GLOBALS['EXEC_TIME'];
                if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() === 13) {
                    $request = $this->getServerRequest();
                    /** @var ?FrontendTypoScript $typoScript */
                    $typoScript = $request->getAttribute('frontend.typoscript');
                    if ($typoScript === null) {
                        $typoScriptConfigArray = [];
                    } else {
                        $typoScriptConfigArray = $typoScript->getConfigArray();
                    }
                    $maxLifetimeOfPage = GeneralUtility::makeInstance(CacheLifetimeCalculator::class)
                        ->calculateLifetimeForPage(
                            $page['uid'],
                            $page,
                            $typoScriptConfigArray,
                            0,
                            $this->context
                        );
                }
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

    protected function getServerRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    protected function getFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
