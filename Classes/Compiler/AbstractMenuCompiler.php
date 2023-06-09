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

use B13\Menus\CacheHelper;
use B13\Menus\Domain\Repository\MenuRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * MenuCompiler sorts out all relevant parts in the constructor which most menu compilers need.
 */
abstract class AbstractMenuCompiler implements SingletonInterface
{
    protected MenuRepository $menuRepository;
    protected CacheHelper $cache;
    protected Context $context;

    public function __construct(Context $context, CacheHelper $cache, MenuRepository $menuRepository)
    {
        $this->context = $context;
        $this->menuRepository = $menuRepository;
        $this->cache = $cache;
    }

    /**
     * Fetch the related pages and caches it via the cache helper.
     */
    abstract public function compile(ContentObjectRenderer $contentObjectRenderer, array $configuration): array;

    /**
     * Create a cache identifier for the cache entry, so this is unique based on
     * - language
     * - logged-in / frontend-usergroups
     * - given configuration
     *
     * @param string $prefix e.g. "tree" or "list".
     * @param array $configuration the menu configuration
     * @return string
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function generateCacheIdentifierForMenu(string $prefix, array $configuration): string
    {
        /** @var UserAspect $frontendUserAspect */
        $frontendUserAspect = $this->context->getAspect('frontend.user');
        $groupIds = $frontendUserAspect->getGroupIds();
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $this->context->getAspect('language');
        $language = $languageAspect->getId();
        /** @var VisibilityAspect $visibilityAspect */
        $visibilityAspect = $this->context->getAspect('visibility');
        $visibility = $visibilityAspect->includeHiddenPages() ? '-with-hidden' : '';
        $root = $this->getCurrentSite()->getRootPageId();
        $identifier = $prefix . '-root-' . $root . '-language-' . $language . '-groups-' . md5(implode('_', $groupIds)) . '-' . $visibility . '-' . substr(md5(json_encode($configuration)), 0, 10);
        return $identifier;
    }

    protected function getCurrentSite(): ?SiteInterface
    {
        return $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
    }

    /**
     * Function to parse typoscript config with stdWrap
     */
    public function parseStdWrap(string $content, array $configuration): string
    {
        $return = GeneralUtility::makeInstance(ContentObjectRenderer::class)->stdWrap($content, $configuration);
        if ($return !== null) {
            return $return;
        }

        return '';
    }
}
