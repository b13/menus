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
        PageRepository::DOKTYPE_SYSFOLDER
    ];

    public function __construct(Context $context = null)
    {
        $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class, $this->context);
    }

    public function getBreadcrumbsMenu(array $originalRootLine): array
    {
        $pages = [];
        $languageAspect = $this->context->getAspect('language');
        foreach ($originalRootLine as $pageInRootLine) {
            $page = $this->pageRepository->getPage((int)$pageInRootLine['uid']);
            if (!$this->pageRepository->isPageSuitableForLanguage($page, $languageAspect)) {
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
        $languageAspect = $this->context->getAspect('language');
        if (!$this->pageRepository->isPageSuitableForLanguage($page, $languageAspect)) {
            return [];
        }
        $this->populateAdditionalKeysForPage($page);
        return $page;
    }
    public function getPageInLanguage(int $pageId, LanguageAspect $languageAspect): array
    {
        $page = $this->pageRepository->getPage($pageId);
        if (!$this->pageRepository->isPageSuitableForLanguage($page, $languageAspect)) {
            return [];
        }
        $this->populateAdditionalKeysForPage($page);
        return $page;
    }

    public function getPageTree(int $startPageId, int $depth, array $configuration): array
    {
        $page = $this->pageRepository->getPage($startPageId);
        $languageAspect = $this->context->getAspect('language');
        if (!$this->pageRepository->isPageSuitableForLanguage($page, $languageAspect)) {
            return [];
        }
        $page['subpages'] = $this->getSubPagesOfPage((int)$page['uid'], $depth, $configuration);
        $this->populateAdditionalKeysForPage($page);
        return $page;
    }

    public function getSubPagesOfPage(int $pageId, int $depth, array $configuration)
    {
        $whereClause = '';
        if (!empty($configuration['excludeDoktypes'])) {
            $excludedDoktypes = array_replace($this->excludedDoktypes, GeneralUtility::intExplode(',', $configuration['excludeDoktypes']));
        } else {
            $excludedDoktypes = $this->excludedDoktypes;
        }
        if (!empty($configuration['excludePages'])) {
            $excludedPages = GeneralUtility::intExplode(',', $configuration['excludePages']);
            $whereClause .= ' AND uid NOT IN (' . implode(',', $excludedPages) . ')';
        }
        $pageTree = $this->pageRepository->getMenu(
            $pageId,
            '*',
            'sorting',
            'AND doktype NOT IN (' . implode(',', $excludedDoktypes) . ') AND nav_hide=0 ' . $whereClause,
            false
        );
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $this->context->getAspect('language');
        foreach ($pageTree as $k => &$page) {
            if (!$this->pageRepository->isPageSuitableForLanguage($page, $languageAspect)) {
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

    protected function populateAdditionalKeysForPage(array &$page): void
    {
        $page['hasSubpages'] = !empty($page['subpages']);
        if ((int)$page['doktype'] === PageRepository::DOKTYPE_SPACER) {
            $page['isSpacer'] = true;
        }
        $page['nav_title'] = $page['nav_title'] ?: $page['title'];
    }
}
