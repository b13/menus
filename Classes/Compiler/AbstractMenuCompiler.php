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
use B13\Menus\Event\CacheIdentifierForMenuEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;

/**
 * MenuCompiler sorts out all relevant parts in the constructor which most menu compilers need.
 */
abstract class AbstractMenuCompiler implements SingletonInterface
{
    protected ?ServerRequestInterface $request = null;

    public function __construct(
        protected Context $context,
        protected CacheHelper $cache,
        protected MenuRepository $menuRepository,
        protected EventDispatcherInterface $eventDispatcher
    ) {
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
        $event = new CacheIdentifierForMenuEvent($identifier);
        $this->eventDispatcher->dispatch($event);
        $identifier = $event->getIdentifier();
        return $identifier;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function getRequest(): ServerRequestInterface
    {
        if ($this->request instanceof ServerRequestInterface) {
            return $this->request;
        }
        throw new ContentRenderingException(
            'PSR-7 request is missing in ContentObjectRenderer. Call setRequest() after object instantiation.',
            1776751007
        );
    }

    protected function getCurrentSite(): ?SiteInterface
    {
        return $this->getRequest()->getAttribute('site');
    }

    /**
     * Function to parse typoscript config with stdWrap
     */
    public function parseStdWrap(string $content, array $configuration): string
    {
        $localCObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $localCObj->setRequest($this->getRequest());
        $return = $localCObj->stdWrap($content, $configuration);
        if ($return !== null) {
            return $return;
        }

        return '';
    }
}
