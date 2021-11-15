<?php

namespace B13\Menus\Tests\Functional\Domain\Repository;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Menus\Domain\Repository\MenuRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class MenuRepositoryTest extends FunctionalTestCase
{

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/menus'
    ];

    /**
     * @test
     */
    public function translatedPageIsNotInMenuIfNavHideIsSet(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/menus/Tests/Functional/Domain/Repository/Fixtures/translated_page_with_nav_hide.xml');
        $languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, 1);
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('language', $languageAspect);
        $menuRepository = GeneralUtility::makeInstance(MenuRepository::class);
        $page = $menuRepository->getPage(1, []);
        $pageInLanguage = $menuRepository->getPageInLanguage(1, $context, []);
        self::assertSame([], $page);
        self::assertSame([], $pageInLanguage);
    }

    /**
     * @test
     */
    public function translatedPageIsInMenuIfNavHideAndIgnoreNavHideIsSet(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/menus/Tests/Functional/Domain/Repository/Fixtures/translated_page_with_nav_hide.xml');
        $languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, 1);
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('language', $languageAspect);
        $menuRepository = GeneralUtility::makeInstance(MenuRepository::class);
        $page = $menuRepository->getPage(1, ['includeNotInMenu' => 1]);
        $pageInLanguage = $menuRepository->getPageInLanguage(1, $context, ['includeNotInMenu' => 1]);
        $page = $this->reduceResults($page);
        $pageInLanguage = $this->reduceResults($pageInLanguage);

        $expectedPage = [
            'uid' => 1,
            'pid' => 0,
            'sys_language_uid' => 1,
            'l10n_parent' => 1,
            'nav_hide' => 1
        ];
        self::assertSame($expectedPage, $page);
        self::assertSame($expectedPage, $pageInLanguage);
    }

    /**
     * @param array $results
     * @return array
     */
    protected function reduceResults(array $result): array
    {
        $keys = ['uid', 'pid', 'sys_language_uid', 'l10n_parent', 'nav_hide'];
        return array_intersect_key($result, array_flip($keys));
    }
}
