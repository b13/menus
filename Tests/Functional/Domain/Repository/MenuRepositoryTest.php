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
        GeneralUtility::makeInstance(Context::class)->setAspect('language', $languageAspect);
        $menuRepository = GeneralUtility::makeInstance(MenuRepository::class);
        $page = $menuRepository->getPage(1, []);
        self::assertSame([], $page);
    }
}
