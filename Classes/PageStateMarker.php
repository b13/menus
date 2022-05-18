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

    public static function markStates(array &$page, int $level = null): void
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
        if (!is_array($GLOBALS['TSFE']->rootLine)) {
            return false;
        }
        foreach ($GLOBALS['TSFE']->rootLine as $pageInRootLine) {
            if ((int)$pageInRootLine['uid'] === $pageId) {
                return true;
            }
        }
        return false;
    }

    private static function isCurrentPage(int $pageId): bool
    {
        return $pageId === $GLOBALS['TSFE']->id;
    }
}
