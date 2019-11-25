Migrate TypoScript for Fluid-based solution
-------------------------------------------

general Menu
    
    -            dataProcessors.10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
    -            dataProcessors.10.level = 2
    +            dataProcessors.10 = B13\Menus\DataProcessing\TreeMenu
    +            dataProcessors.10.depth = 2
    
special directory

    -            dataProcessors.10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
    -            dataProcessors.special = directory
    -            dataProcessors.special.value = 34
    +            dataProcessors.dataProcessors.10 = B13\Menus\DataProcessing\TreeMenu
    +            dataProcessors.entryPoints = 34
    
special list

    -            dataProcessors.10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
    -            dataProcessors.special = list
    -            dataProcessors.special.value = 34,22
    +            dataProcessors.10 = B13\Menus\DataProcessing\ListMenu
    +            dataProcessors.pages = 34,22

special language

    -            dataProcessors.10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
    -            dataProcessors.special = language
    +            dataProcessors.10 = B13\Menus\DataProcessing\LanguageMenu
    
special breadcrumbs

    -            dataProcessors.10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
    -            dataProcessors.10.special = rootline
    +            dataProcessors.10 = B13\Menus\DataProcessing\BreadcrumbsMenu
    
    
Migrate Templates for Fluid-based solution
-------------------------------------------

* menuItem.children -> menuItem.hasSubpages or menuItem.subpages
* menuItem.data -> is dropped, properties direct in menuItem
* menuItem.link -> is dropped, use Page-Link-VH with menuItem.uid
* menuItem.spacer -> menuItem.isSpacer
* menuItem.current -> menuItem.isCurrentPage
* menuItem.active -> menuItem.isInRootLine
* menuItem.title -> menuItem.nav_title

    
