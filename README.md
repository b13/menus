![CI](https://github.com/b13/menus/actions/workflows/ci.yml/badge.svg)

# Menus - A TYPO3 Extension for creating fast menus - in a fast way

## Introduction

TYPO3 CMS is known for handling large websites with lots of content. TYPO3 Core provides several ways to build
navigation / menus in a very flexible way. However, generating menus has been a tedious issue in most of our
large-scale projects. With TYPO3 v9, the performance of generating menus improved when it comes to URL generation,
but a few conceptual issues within linking and menu generation still exist:

1. All logic relies on HMENU

   Every menu is generated using HMENU, even the MenuDataProcessor for Fluid is using this. Yes, it's powerful, but also
   offers a lot of options we do not need in most circumstances.

2. HMENU saves states for each page

   HMENU offers the possibility to define A LOT of states ("active", "current", "has children"). This information is
   different for each page - obviously - which is then cached in a separate cache entry in `cache_hash` - making
   the cache entries fairly large even though we do not use states.

   We use `expAll` (expand all subpages for all other pages as well) which makes the requests to the pages
   enormously large.

3. HMENU has a cryptic syntax for "special" menus

    Nowadays, it is fairly common to build menus for footer navigation, mega-menus, sitemap-like menus for an additional
    sidebar. Using "special." for language menus, for "directories" or just a simple list of pages, seems rather complex.


This extension tries to overcome these pitfalls by
 * building menus once, then caches the results and afterwards applying active states (reduce amount of cached data).
   This is especially important for Tree-based menus,
 * introducing new cObjects and DataProcessors for the specific use cases making them more understandable for
   non-TYPO3-Gurus.

## Installation & Requirements

Use `composer req b13/menus` or install it via TYPO3's Extension Manager from the
[TYPO3 Extension Repository](https://extensions.typo3.org) using the extension key `menus`.

You need TYPO3 v9 with Site Handling for this extension to work. If your project supports mount points,
this is not implemented. In addition, pages to access restricted pages (even though no access exists) are not yet
considered.

## Features

The extension ships TypoScript cObjects and TypoScript DataProcessors for Fluid-based page templates.

### Common Options for all menus

* excludePages - a list of page IDs (and their subpages if Tree Menu or Breadcrumbs is used) to exclude from the page
* excludeDoktypes - a list of doktypes that are not rendered. BE_USER_SECTIONs are excluded by default. SYS_FOLDERs are queried (for subpages etc) but never rendered.
* includeNotInMenu - include pages with nav_hide set to 1, instead of ignoring them

### Common options for items

In TypoScript this is available as `field:isSpacer`, in Fluid, this is accessible in `{page.isSpacer}`

    - {page.isCurrentPage} (bool)
    - {page.isInRootLine} (bool)
    - {page.isSpacer} (bool)
    - {page.hasSubpages} (bool) - TreeMenu only
    - {page.subpages} (array) - TreeMenu only

### Tree Menu

Use this for mega menus, or separate menus for mobile devices, like sitemaps.

Pure TypoScript-based solution:

    page.10 = TREEMENU
    # a list of page IDs, rootpageID is used if none given
    page.10.entryPoints = 23,13
    # the number of levels to fetch from the database (1 if empty)
    page.10.depth = 3
    page.10.excludePages = 4,51
    # 0: default, 1 to include nav_hide = 1 pages
    page.10.includeNotInMenu = 0
    page.10.renderObj.level0 = TEXT
    page.10.renderObj.level0.typolink.parameter.data = field:uid
    page.10.renderObj.level0.typolink.ATagParams = class="active"
    page.10.renderObj.level0.typolink.ATagParams.if.isTrue.field = isInRootLine
    page.10.renderObj.level0.dataWrap = <li class="firstLevel">|<ul>{field:subpageContent}</ul></li>

Fluid-based solution:

    page.10 = FLUIDTEMPLATE
    page.10.dataProcessing.10 = B13\Menus\DataProcessing\TreeMenu
    page.10.dataProcessing.10.entryPoints = 23,13
    page.10.dataProcessing.10.depth = 3
    page.10.dataProcessing.10.excludePages = 4,51
    # 0: default, 1 to include nav_hide = 1 pages
    page.10.dataProcessing.10.includeNotInMenu = 0
    page.10.dataProcessing.10.as = mobilemenu

Usage in Fluid:

    <nav>
        <f:for each="{mobilemenu}" as="page">
            <f:link.page pageUid="{page.uid}">{page.nav_title}</f:link.page>
            <f:if condition="{page.hasSubpages} && {page.isInRootLine}">
                <ul>
                    <f:for each="{page.subpages}" as="subpage">
                        <li><f:link.page pageUid="{subpage.uid}">{subpage.nav_title}</f:link.page>
                    </f:for>
                </ul>
            </f:if>
        </f:for>
    </nav>

**Note**: nav_title is title if Database-Record nav_title is empty.

### Language Menu

Building a language switcher can be achieved by a few lines of code:

Pure TypoScript solution:

    page.10 = LANGUAGEMENU
    page.10.excludeLanguages = de,en
    # 0: default, 1 to include nav_hide = 1 pages
    page.10.includeNotInMenu = 0
    # add all siteLanguages to menu even if page is not available in language (default 0)
    page.10.addAllSiteLanguages = 1
    page.10.wrap = <ul> | </ul>
    page.10.renderObj.typolink.parameter.data = field:uid
    page.10.renderObj.typolink.additionalParams.data = field:language|languageId
    page.10.renderObj.typolink.additionalParams.intval = 1
    page.10.renderObj.typolink.additionalParams.wrap = &L=|
    page.10.renderObj.data = field:language|title // field:language|twoLetterIsoCode
    page.10.renderObj.wrap = <li class="language-item"> | </li>

The stdWrap `data` is the information of the current page plus the information merged from the selected SiteLanguage.

Fluid-based solution:

    page.10 = FLUIDTEMPLATE
    page.10.dataProcessing.10 = B13\Menus\DataProcessing\LanguageMenu
    page.10.dataProcessing.10.excludeLanguages = de,en
    # 0: default, 1 to include nav_hide = 1 pages
    page.10.dataProcessing.10.includeNotInMenu = 0
    # add all siteLanguages to menu even if page is not available in language (default 0)
    page.10.dataProcessing.10.addAllSiteLanguages = 1
    page.10.dataProcessing.10.as = languageswitcher

Usage in Fluid:

    <nav>
        <f:for each="{languageswitcher}" as="item">
            <f:link.page pageUid="{item.uid}" language="{item.language.languageId}">{item.language.title}</f:link.page>
        </f:for>
    </nav>

**Note**: the languageMenu hold the siteLanguage on each item in the `language` property as an array

### List Menu

If you just want a list of all items within a folder, or for a link list in the footer, use the List Menu.

Pure TypoScript-based solution:

    page.10 = LISTMENU
    # a page ID, rootpageID is used if none given, stdWrap possible
    page.10.pages = 13,14,15
    # 0: default, 1 to include nav_hide = 1 pages
    page.10.includeNotInMenu = 0
    page.10.wrap = <ul> | </ul>
    page.10.renderObj = TEXT
    page.10.renderObj.typolink.parameter.data = field:uid
    page.10.renderObj.wrap = <li> | </li>

Fluid-based solution:

    page.10 = FLUIDTEMPLATE
    page.10.dataProcessing.10 = B13\Menus\DataProcessing\ListMenu
    page.10.dataProcessing.10.pages = 13,14,15
    page.10.dataProcessing.10.as = footerlinks
    # 0: default, 1 to include nav_hide = 1 pages
    page.10.dataProcessing.10.includeNotInMenu = 0

Usage in Fluid:

    <nav>
        <f:for each="{footerlinks}" as="page">
            <f:link.page pageUid="{page.uid}">{page.nav_title}</f:link.page>
        </f:for>
    </nav>


### Breadcrumb Menu (a.k.a. Rootline Menu)

    page.10 = BREADCRUMBS
    page.10.excludePages = 4,51
    # 0: default, 1 to include nav_hide = 1 pages
    page.10.includeNotInMenu = 0
    page.10.wrap = <ul> | </ul>
    page.10.renderObj = TEXT
    page.10.renderObj.typolink.parameter.data = field:uid
    page.10.renderObj.wrap = <li> | </li>


Fluid-based solution:

    page.10 = FLUIDTEMPLATE
    page.10.dataProcessing.10 = B13\Menus\DataProcessing\BreadcrumbsMenu
    page.10.dataProcessing.10.excludePages = 4,51
    # 0: default, 1 to include nav_hide = 1 pages
    page.10.dataProcessing.10.includeNotInMenu = 0
    page.10.dataProcessing.10.as = breadcrumbs

Usage in Fluid:

    <nav>
        <f:for each="{breadcrumbs}" as="page">
            <f:link.page pageUid="{page.uid}">{page.nav_title}</f:link.page>
            <f:if condition="{page.isCurrentPage} == false"> &nbsp; </f:if>
        </f:for>
    </nav>

### Dynamic configuration values for the menu (stdWrap)

If you want to get a menu of the direct siblings of a page, no matter what page you have selected, you can use the stdWrap functions built into each property:

	9999 = B13\Menus\DataProcessing\TreeMenu
	9999 {
		entryPoints.data = page:pid
		as = listOfJobPages
	}

By using the `.data` property of the `entryPoints` attribute we can access each property of the currently build page. And so we can render the siblings of the page.

## Technical Details

### Caching

Fetching the records is cached in a cache entry (with proper cache tags) within "cache_hash",
and the rendering is also cached in a separated cache entry within "cache_pages" for each page (regular),
where active state is applied.

### FAQ

This extension refrains from handling options `addQueryParams`, or `ADD_GET_PARAM`, or the `target` property
in order to deal with the pages as "native" as possible, like any other link.

## License

The extension is licensed under GPL v2+, same as the TYPO3 Core. For details see the LICENSE file in this repository.

## Open Issues

If you find an issue, feel free to create an issue on GitHub or a pull request.

### ToDos
- add `includeSpacer` option
- extract stdWrap functionality out of caching parameters

### Credits

This extension was created by [Benni Mack](https://github.com/bmack) in 2019 for [b13 GmbH](https://b13.com).

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure long-term performance, reliability, and results in all our code.
