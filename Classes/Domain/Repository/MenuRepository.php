<?php

declare(strict_types=1);
namespace B13\Menus\Domain\Repository;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Responsible for interacting with the PageRepository class, in addition, should be responsible for overlays
 * and static additional properties (isSpacer) that can be cached away.
 */
class MenuRepository
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    // Never show or query them.
    protected $excludedDoktypes = [
        PageRepository::DOKTYPE_BE_USER_SECTION,
        PageRepository::DOKTYPE_RECYCLER,
        PageRepository::DOKTYPE_SYSFOLDER,
    ];

    public function __construct(Context $context = null, PageRepository $pageRepository = null)
    {
        $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
        $this->pageRepository = $pageRepository ?? GeneralUtility::makeInstance(PageRepository::class, $this->context);
    }

    public function getBreadcrumbsMenu(array $originalRootLine, array $configuration): array
    {
        $pages = [];
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $this->context->getAspect('language');
        $excludeDoktypes = $this->getExcludeDoktypes($configuration);
        $excludedPagesArray = $this->getExcludePages($configuration);
        foreach ($originalRootLine as $pageInRootLine) {
            // check for excluded page before useless retrieving page record
            if ($excludedPagesArray && in_array((int)$pageInRootLine['uid'], $excludedPagesArray)) {
                continue;
            }
            $page = $this->pageRepository->getPage((int)$pageInRootLine['uid']);
            if (!$this->isPageSuitableForLanguage($page, $languageAspect, $configuration)) {
                continue;
            }
            if (!isset($page['doktype']) || in_array($page['doktype'], $excludeDoktypes)) {
                continue;
            }
            $this->populateAdditionalKeysForPage($page);
            $pages[] = $page;
        }
        return array_reverse($pages);
    }

    public function getPage(int $pageId, array $configuration): array
    {
        $page = $this->pageRepository->getPage($pageId);
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $this->context->getAspect('language');
        if (!$this->isPageSuitableForLanguage($page, $languageAspect, $configuration)) {
            return [];
        }
        $this->populateAdditionalKeysForPage($page);
        return $page;
    }

    public function getPageInLanguage(int $pageId, Context $context, array $configuration): array
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class, $context);
        $page = $pageRepository->getPage($pageId);
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $context->getAspect('language');
        if (!$this->isPageIncludable($page, $configuration) || !$pageRepository->isPageSuitableForLanguage($page, $languageAspect)) {
            return [];
        }
        $this->populateAdditionalKeysForPage($page);
        return $page;
    }

    public function getPageTree(int $startPageId, int $depth, array $configuration): array
    {
        $page = $this->pageRepository->getPage($startPageId);
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $this->context->getAspect('language');
        if (!$this->isPageSuitableForLanguage($page, $languageAspect, $configuration)) {
            return [];
        }
        $page['subpages'] = $this->getSubPagesOfPage((int)$page['uid'], $depth, $configuration);
        $this->populateAdditionalKeysForPage($page);
        return $page;
    }

    /**
     * @param array $configuration
     * @return array
     */
    protected function getExcludeDoktypes(array $configuration): array
    {
        if (!empty($configuration['excludeDoktypes'])) {
            $excludedDoktypes = array_unique(array_merge($this->excludedDoktypes, GeneralUtility::intExplode(',', $configuration['excludeDoktypes'])));
        } else {
            $excludedDoktypes = $this->excludedDoktypes;
        }
        return $excludedDoktypes;
    }

    /**
     * @param array $configuration
     * @return array
     */
    protected function getExcludePages(array $configuration): ?array
    {
        $excludePages = null;
        if (!empty($configuration['excludePages'])) {
            $excludePages = array_unique(GeneralUtility::intExplode(',', $configuration['excludePages']));
        }
        return empty($excludePages) ? null : $excludePages;
    }

    /**
     * @param array $configuration
     * @return bool
     */
    protected function getIncludeNotInMenu(array $configuration): bool
    {
        return 1 === (int)($configuration['includeNotInMenu'] ?? 0);
    }

    public function getSubPagesOfPage(int $pageId, int $depth, array $configuration)
    {
        $whereClause = '';

        if (!empty($configuration['excludePages'])) {
            $excludedPagesArray = GeneralUtility::intExplode(',', $configuration['excludePages']);
            $whereClause .= ' AND uid NOT IN (' . implode(',', $excludedPagesArray) . ')';
        }
        $excludedDoktypes = $this->getExcludeDoktypes($configuration);
        $pageTree = $this->pageRepository->getMenu(
            $pageId,
            '*',
            'sorting',
            'AND doktype NOT IN (' . implode(',', $excludedDoktypes) . ') ' . $whereClause,
            false
        );
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $this->context->getAspect('language');
        foreach ($pageTree as $k => &$page) {
            if (!$this->isPageSuitableForLanguage($page, $languageAspect, $configuration)) {
                unset($pageTree[$k]);
                continue;
            }
            if ($depth > 0) {
                $page['subpages'] = $this->getSubPagesOfPage((int)$page['uid'], $depth-1, $configuration);
            }
            $this->populateAdditionalKeysForPage($page);
        }
        return $pageTree;
    }

    protected function isPageSuitableForLanguage(array $page, LanguageAspect $languageAspect, array $configuration): bool
    {
        return $this->isPageIncludable($page, $configuration) && $this->pageRepository->isPageSuitableForLanguage($page, $languageAspect);
    }

    protected function isPageIncludable(array $page, array $configuration): bool
    {
        if ($page === []) {
            return false;
        }
        return $this->getIncludeNotInMenu($configuration) || (int)$page['nav_hide'] !== 1;
    }

    protected function populateAdditionalKeysForPage(array &$page): void
    {
        $page['hasSubpages'] = !empty($page['subpages']);
        if ((int)$page['doktype'] === PageRepository::DOKTYPE_SPACER) {
            $page['isSpacer'] = true;
        }
        $page['nav_title'] = $page['nav_title'] ?: $page['title'];
    }
}
