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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Frontend\Page\PageInformation;

/**
 * Helper class to set additional properties for a page
 */
class PageStateMarker
{
    public static function markStatesRecursively(array &$page, int $level): void
    {
        $page['level'] = $level;
        if (!empty($page['subpages'])) {
            foreach ($page['subpages'] as &$subPage) {
                self::markStatesRecursively($subPage, $level+1);
            }
        }
        self::markStates($page, $level);
    }

    public static function markStates(array &$page, ?int $level = null): void
    {
        if ($level !== null) {
            $page['level'] = $level;
        }
        if (!isset($page['uid'])) {
            $page['isInRootLine'] = false;
            $page['isCurrentPage'] = false;
            return;
        }
        $page['isInRootLine'] = self::isPageInCurrentRootLine((int)$page['uid']);
        $page['isCurrentPage'] = self::isCurrentPage((int)$page['uid']);
    }

    private static function isPageInCurrentRootLine(int $pageId): bool
    {
        if ((new Typo3Version())->getMajorVersion() < 13) {
            if (!is_array($GLOBALS['TSFE']->rootLine)) {
                return false;
            }
            return in_array($pageId, array_column($GLOBALS['TSFE']->rootLine, 'uid'));
        }
        $pageInformation = self::getPageInformationFromRequest();
        if ($pageInformation === null) {
            return false;
        }
        return in_array($pageId, array_column($pageInformation->getRootLine(), 'uid'), true);
    }

    private static function isCurrentPage(int $pageId): bool
    {
        if ((new Typo3Version())->getMajorVersion() < 13) {
            return $pageId === $GLOBALS['TSFE']->id;
        }
        $pageInformation = self::getPageInformationFromRequest();
        if ($pageInformation === null) {
            return false;
        }
        return $pageInformation->getId() === $pageId;
    }

    private static function getPageInformationFromRequest(): ?PageInformation
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if ($request instanceof ServerRequestInterface) {
            /** @var ?PageInformation $pageInformation */
            $pageInformation = $request->getAttribute('frontend.page.information');
            return $pageInformation;
        }
        return null;
    }
}
