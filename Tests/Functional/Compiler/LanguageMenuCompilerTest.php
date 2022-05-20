<?php

namespace B13\Menus\Tests\Functional\Compiler;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Menus\Compiler\LanguageMenuCompiler;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class LanguageMenuCompilerTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/menus',
    ];

    protected $pathsToLinkInTestInstance = [
        'typo3conf/ext/menus/Build/sites' => 'typo3conf/sites',
    ];

    protected $defaultPageDataSet = [
        'defaultPage' => [
            'uid' => 1,
            'pid' => 0,
        ],
        'dePage' => [
            'uid' => 2,
            'pid' => 0,
            'l10n_parent' => 1,
            'l10n_source' => 1,
            'sys_language_uid' => 1,
        ],
        'frPage' => [
            'uid' => 3,
            'pid' => 0,
            'l10n_parent' => 1,
            'l10n_source' => 1,
            'sys_language_uid' => 2,
        ],
    ];

    /**
     * @test
     */
    public function allPagesTranslatedTest(): void
    {
        $pageDataset = $this->defaultPageDataSet;
        $menu = $this->compileMenu($pageDataset);
        self::assertSame(3, count($menu), 'all languages should be included in menu');
        foreach ($menu as $item) {
            self::assertTrue(isset($item['uid']), 'uid should be set');
            self::assertTrue(isset($item['language']), 'language should be set');
        }
    }

    /**
     * @test
     */
    public function dePageNavHideAllSiteLanguages(): void
    {
        $pageDataset = $this->defaultPageDataSet;
        $pageDataset['dePage']['nav_hide'] = 1;
        $menu = $this->compileMenu($pageDataset, ['addAllSiteLanguages' => 1]);
        self::assertSame(3, count($menu), 'all languages should be included in menu');
        foreach ($menu as $item) {
            self::assertTrue(isset($item['language']), 'language should be set');
            if ($item['language']['typo3Language'] === 'de') {
                self::assertFalse(isset($item['uid']));
            } else {
                self::assertTrue(isset($item['uid']));
            }
        }
    }

    /**
     * @test
     */
    public function dePageNavHide(): void
    {
        $pageDataset = $this->defaultPageDataSet;
        $pageDataset['dePage']['nav_hide'] = 1;
        $menu = $this->compileMenu($pageDataset);
        self::assertSame(2, count($menu), 'only two items should be in menu');
        foreach ($menu as $item) {
            self::assertTrue(isset($item['language']), 'language should be set');
            self::assertTrue($item['language']['typo3Language'] !== 'de', 'de should not be included in menu');
        }
    }

    /**
     * @test
     */
    public function dePageNotTranslatedAllSiteLanguages(): void
    {
        $pageDataset = $this->defaultPageDataSet;
        unset($pageDataset['dePage']);
        $menu = $this->compileMenu($pageDataset, ['addAllSiteLanguages' => 1]);
        self::assertSame(3, count($menu), 'all languages should be included in menu');
        foreach ($menu as $item) {
            self::assertTrue(isset($item['language']), 'language should be set');
            if ($item['language']['typo3Language'] === 'de') {
                self::assertFalse(isset($item['uid']), 'de should not be included in menu');
            } else {
                self::assertTrue(isset($item['uid']), $item['language']['typo3Language'] . ' should be included in menu');
            }
        }
    }

    /**
     * @test
     */
    public function dePageNotTranslated(): void
    {
        $pageDataset = $this->defaultPageDataSet;
        unset($pageDataset['dePage']);
        $menu = $this->compileMenu($pageDataset);
        self::assertSame(2, count($menu), 'only two items should be in menu');
        foreach ($menu as $item) {
            self::assertTrue(isset($item['language']), 'language should be set');
            self::assertTrue($item['language']['typo3Language'] !== 'de', 'de should not be included in menu');
        }
    }

    /**
     * @test
     */
    public function hideDefaultLanguageOfPageAllSiteLanguages(): void
    {
        $pageDataset = $this->defaultPageDataSet;
        $pageDataset['defaultPage']['l18n_cfg'] = 1;
        $menu = $this->compileMenu($pageDataset, ['addAllSiteLanguages' => 1]);
        self::assertSame(3, count($menu), 'all languages should be included in menu');
        foreach ($menu as $item) {
            self::assertTrue(isset($item['language']), 'language should be set');
            if ($item['language']['typo3Language'] === 'default') {
                self::assertFalse(isset($item['uid']), 'default should not be included in menu');
            } else {
                self::assertTrue(isset($item['uid']), $item['language']['typo3Language'] . ' should be included in menu');
            }
        }
    }

    /**
     * @test
     */
    public function hideDefaultLanguageOfPage(): void
    {
        $pageDataset = $this->defaultPageDataSet;
        $pageDataset['defaultPage']['l18n_cfg'] = 1;
        $menu = $this->compileMenu($pageDataset);
        self::assertSame(2, count($menu), 'only two items should be in menu');
        foreach ($menu as $item) {
            self::assertTrue(isset($item['language']), 'language should be set');
            self::assertTrue($item['language']['typo3Language'] !== 'default', 'default should not be included in menu');
        }
    }

    /**
     * @test
     */
    public function frFallbackTest(): void
    {
        $pageDataset = $this->defaultPageDataSet;
        unset($pageDataset['frPage']);
        $menu = $this->compileMenu($pageDataset);
        self::assertSame(3, count($menu), 'all languages should be included in menu');
        foreach ($menu as $item) {
            self::assertTrue(isset($item['language']), 'language should be set');
            if ($item['language']['typo3Language'] === 'fr') {
                self::assertTrue(isset($item['uid']), 'fr should be included in menu');
                self::assertSame($pageDataset['defaultPage']['uid'], $item['uid']);
            } else {
                self::assertTrue(isset($item['uid']), $item['language']['typo3Language'] . ' should be included in menu');
            }
        }
    }

    /**
     * @test
     */
    public function deIsExcludedLanguageAllSiteLanguages(): void
    {
        $pageDataset = $this->defaultPageDataSet;
        $menu = $this->compileMenu($pageDataset, ['excludeLanguages' => 'de', 'addAllSiteLanguages' => '1']);
        self::assertSame(2, count($menu), 'only two pages should be included in menu');
    }

    /**
     * @test
     */
    public function deIsExcludedLanguage(): void
    {
        $pageDataset = $this->defaultPageDataSet;
        $menu = $this->compileMenu($pageDataset, ['excludeLanguages' => 'de']);
        self::assertSame(2, count($menu), 'only two pages should be included in menu');
    }

    protected function compileMenu(array $pageDataset, array $configuration = []): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        foreach ($pageDataset as $page) {
            $connection->insert('pages', $page);
        }
        $controller = $this->getAccessibleMock(
            TypoScriptFrontendController::class,
            ['get_cache_timeout'],
            [],
            '',
            false
        );
        $GLOBALS['TSFE'] = $controller;
        $GLOBALS['TSFE']->id = '1';
        $contentObjectRenderer = new ContentObjectRenderer();
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByIdentifier('main');
        $languageMenuCompiler = $this->getAccessibleMock(
            LanguageMenuCompiler::class,
            [
                'generateCacheIdentifierForMenu',
                'getCurrentSite',
            ]
        );
        $languageMenuCompiler->expects(self::any())->method('generateCacheIdentifierForMenu')->willReturn('foo');
        $languageMenuCompiler->expects(self::any())->method('getCurrentSite')->willReturn($site);
        $menu = $languageMenuCompiler->compile($contentObjectRenderer, $configuration);
        return $menu;
    }
}
