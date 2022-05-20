<?php

namespace B13\Menus\Tests\Unit\Domain\Repository;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Menus\Domain\Repository\MenuRepository;
use Prophecy\Argument;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class MenuRepositoryTest extends UnitTestCase
{

    /**
     * @test
     */
    public function getSubPagesOfPageRestrictQueryToExcludeDoktypes(): void
    {
        $context = $this->prophesize(Context::class);
        $context->getAspect('language')->willReturn($this->prophesize(LanguageAspect::class)->reveal());
        $pageRepository = $this->prophesize(PageRepository::class);
        $excludedDoktypes = [
            PageRepository::DOKTYPE_BE_USER_SECTION,
            PageRepository::DOKTYPE_RECYCLER,
            PageRepository::DOKTYPE_SYSFOLDER,
        ];
        $pageRepository->getMenu(1, '*', 'sorting', Argument::any(), false)->willReturn([]);
        $pageRepository->getMenu(1, '*', 'sorting', 'AND doktype NOT IN (' . implode(',', $excludedDoktypes) . ') ', false)->shouldBeCalled()->willReturn([]);

        $menuRepository = $this->getMockBuilder(MenuRepository::class)
            ->setMethods(['foo'])
            ->setConstructorArgs([$context->reveal(), $pageRepository->reveal()])
            ->getMock();
        $menuRepository->getSubPagesOfPage(1, 1, []);
    }

    /**
     * @test
     */
    public function getSubPagesOfPageMergeExcludeDoktypesFromConfiguration(): void
    {
        $context = $this->prophesize(Context::class);
        $context->getAspect('language')->willReturn($this->prophesize(LanguageAspect::class)->reveal());
        $pageRepository = $this->prophesize(PageRepository::class);
        $excludedDoktypes = [
            PageRepository::DOKTYPE_BE_USER_SECTION,
            PageRepository::DOKTYPE_RECYCLER,
            PageRepository::DOKTYPE_SYSFOLDER,
        ];
        $pageRepository->getMenu(1, '*', 'sorting', Argument::any(), false)->willReturn([]);
        $pageRepository->getMenu(1, '*', 'sorting', 'AND doktype NOT IN (' . implode(',', $excludedDoktypes) . ',99) ', false)->shouldBeCalled()->willReturn([]);

        $menuRepository = $this->getMockBuilder(MenuRepository::class)
            ->setMethods(['foo'])
            ->setConstructorArgs([$context->reveal(), $pageRepository->reveal()])
            ->getMock();
        $menuRepository->getSubPagesOfPage(1, 1, ['excludeDoktypes' => 99]);
    }

    /**
     * @test
     */
    public function getBreadcrumbsMenuRespectConfiguredExcludeDoktypes(): void
    {
        $rootLine = [
            ['uid' => 1, 'doktype' => 99, 'nav_hide'=> 0],
            ['uid' => 2, 'doktype' => 98, 'nav_hide'=> 0],
        ];
        $context = $this->prophesize(Context::class);
        $context->getAspect('language')->willReturn($this->prophesize(LanguageAspect::class)->reveal());
        $pageRepository = $this->prophesize(PageRepository::class);
        $pageRepository->getPage(1)->willReturn($rootLine[0]);
        $pageRepository->getPage(2)->willReturn($rootLine[1]);
        $pageRepository->isPageSuitableForLanguage(Argument::any(), Argument::any())->willReturn(true);
        $menuRepository = $this->getMockBuilder(MenuRepository::class)
            ->setMethods(['populateAdditionalKeysForPage'])
            ->setConstructorArgs([$context->reveal(), $pageRepository->reveal()])
            ->getMock();
        $breadcrumbs = $menuRepository->getBreadcrumbsMenu($rootLine, ['excludeDoktypes' => 99]);
        self::assertSame(1, count($breadcrumbs));
    }
}
