Migrate TypoScript for Fluid-based solution
-------------------------------------------

general Menu

    -            dataProcessing.10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
    -            dataProcessing.10.level = 2
    +            dataProcessing.10 = B13\Menus\DataProcessing\TreeMenu
    +            dataProcessing.10.depth = 2

special directory

    -            dataProcessing.10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
    -            dataProcessing.special = directory
    -            dataProcessing.special.value = 34
    +            dataProcessing.10 = B13\Menus\DataProcessing\TreeMenu
    +            dataProcessing.entryPoints = 34

special list

    -            dataProcessing.10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
    -            dataProcessing.special = list
    -            dataProcessing.special.value = 34,22
    +            dataProcessing.10 = B13\Menus\DataProcessing\ListMenu
    +            dataProcessing.pages = 34,22

special language

    -            dataProcessing.10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
    -            dataProcessing.special = language
    +            dataProcessing.10 = B13\Menus\DataProcessing\LanguageMenu

special breadcrumbs

    -            dataProcessing.10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
    -            dataProcessing.10.special = rootline
    +            dataProcessing.10 = B13\Menus\DataProcessing\BreadcrumbsMenu


Migrate Templates for Fluid-based solution
-------------------------------------------

* menuItem.children -> menuItem.hasSubpages or menuItem.subpages
* menuItem.data -> is dropped, properties direct in menuItem
* menuItem.link -> is dropped, use Page-Link-VH with menuItem.uid
* menuItem.spacer -> menuItem.isSpacer
* menuItem.current -> menuItem.isCurrentPage
* menuItem.active -> menuItem.isInRootLine
* menuItem.title -> menuItem.nav_title


