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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This hook is triggered before caches for a page get flushed.
 *
 * Ideally this should not trigger the cache flush but only allow to add tags.
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
        if ($params['table'] !== 'pages' || empty($params['uid_page'])) {
            return;
        }
        $pageId = (int)$params['uid_page'];
        // If the current page has subpages, it can be flushed directly
        if ($this->hasSubpages($pageId)) {
            $menuTag = 'menuId_' . $pageId;
        } else {
            // Page is a "leave" in the tree, so flush the menu ID of the parent page
            $parentPageId = $dataHandler->getPID('pages', $pageId);
            $menuTag = 'menuId_' . $parentPageId;
        }
        $this->cacheHash->flushByTag($menuTag);
        $this->cachePages->flushByTag($menuTag);
    }

    protected function hasSubpages(int $pageId): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $result = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageId, \PDO::PARAM_INT)))
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        return !(empty($result));
    }
}
