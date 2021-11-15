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
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * MenuCompiler sorts out all relevant parts in the constructor which most menu compilers need.
 */
abstract class AbstractMenuCompiler
{
    /**
     * @var MenuRepository
     */
    protected $menuRepository;

    /**
     * @var CacheHelper
     */
    protected $cache;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context = null, CacheHelper $cache = null)
    {
        $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
        $this->menuRepository = GeneralUtility::makeInstance(MenuRepository::class, $this->context);
        $this->cache = $cache ?? GeneralUtility::makeInstance(CacheHelper::class);
    }

    /**
     * Fetch the related pages and caches it via the cache helper.
     *
     * @param ContentObjectRenderer $contentObjectRenderer
     * @param array $configuration
     * @return array
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
        $groupIds = $this->context->getAspect('frontend.user')->getGroupIds();
        $language = $this->context->getAspect('language')->getId();
        $root = $this->getCurrentSite()->getRootPageId();
        $identifier = $prefix . '-root-' . $root . '-language-' . $language . '-groups-' . implode('_', $groupIds) . '-' . GeneralUtility::shortMD5(json_encode($configuration));
        return $identifier;
    }

    protected function getCurrentSite(): ?SiteInterface
    {
        return $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
    }

    /**
     * Function to parse typoscript config with stdWrap
     * @param string $content
     * @param string $configuration
     *
     * @return string
     */
    public function parseStdWrap($content, $configuration): string
    {
        $return = GeneralUtility::makeInstance(ContentObjectRenderer::class)->stdWrap($content, $configuration);
        if ($return !== null) {
            return $return;
        }

        return '';
    }
}
