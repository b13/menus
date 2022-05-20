<?php

defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] = array_merge($GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'], [
    'TREEMENU' => \B13\Menus\ContentObject\TreeMenuContentObject::class,
    'LISTMENU' => \B13\Menus\ContentObject\ListMenuContentObject::class,
    'LANGUAGEMENU' => \B13\Menus\ContentObject\LanguageMenuContentObject::class,
    'BREADCRUMBS' => \B13\Menus\ContentObject\BreadcrumbsContentObject::class,
]);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['tx-menus'] =
    \B13\Menus\Hooks\DataHandlerHook::class . '->clearMenuCaches';

if (!class_exists(\TYPO3\CMS\Frontend\Page\PageRepository::class)) {
    class_alias(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class, \TYPO3\CMS\Frontend\Page\PageRepository::class);
}
