<?php

declare(strict_types=1);

namespace B13\Menus\Tests\Unit\Domain\Repository;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Menus\Domain\Repository\MenuRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class MenuRepositoryTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    /**
     * @test
     */
    public function getSubPagesOfPageRestrictQueryToExcludeDoktypes(): void
    {
        $languageAspect = $this->getMockBuilder(LanguageAspect::class)
            ->getMock();
        $context = $this->getMockBuilder(Context::class)
            ->getMock();
        $context->expects(self::once())->method('getAspect')->with('language')->willReturn($languageAspect);
        $pageRepository = $this->getMockBuilder(PageRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $excludedDoktypes = [
            PageRepository::DOKTYPE_BE_USER_SECTION,
            PageRepository::DOKTYPE_RECYCLER,
            PageRepository::DOKTYPE_SYSFOLDER,
        ];
        $pageRepository->expects(self::once())->method('getMenu')
            ->with(1, '*', 'sorting', 'AND doktype NOT IN (' . implode(',', $excludedDoktypes) . ') ', false)
            ->willReturn([]);
        $menuRepository = new MenuRepository($context, $pageRepository, $this->createMock(EventDispatcherInterface::class));
        $menuRepository->getSubPagesOfPage(1, 1, []);
    }

    /**
     * @test
     */
    public function getSubPagesOfPageMergeExcludeDoktypesFromConfiguration(): void
    {
        $languageAspect = $this->getMockBuilder(LanguageAspect::class)
            ->getMock();
        $context = $this->getMockBuilder(Context::class)
            ->getMock();
        $context->expects(self::once())->method('getAspect')->with('language')->willReturn($languageAspect);
        $pageRepository = $this->getMockBuilder(PageRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $excludedDoktypes = [
            PageRepository::DOKTYPE_BE_USER_SECTION,
            PageRepository::DOKTYPE_RECYCLER,
            PageRepository::DOKTYPE_SYSFOLDER,
        ];
        $pageRepository->expects(self::once())->method('getMenu')
            ->with(1, '*', 'sorting', 'AND doktype NOT IN (' . implode(',', $excludedDoktypes) . ',99) ', false)
            ->willReturn([]);
        $menuRepository = new MenuRepository($context, $pageRepository, $this->createMock(EventDispatcherInterface::class));
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
        $languageAspect = $this->getMockBuilder(LanguageAspect::class)
            ->getMock();
        $context = $this->getMockBuilder(Context::class)
            ->getMock();
        $context->expects(self::once())->method('getAspect')->with('language')->willReturn($languageAspect);

        $pageRepository = new class() extends PageRepository {
            public function getPage($uid, $disableGroupAccessCheck = false)
            {
                if ($uid === 1) {
                    // $rootLine[0]
                    return ['uid' => 1, 'doktype' => 99, 'nav_hide'=> 0];
                }
                if ($uid === 2) {
                    // $rootLine[0]
                    return ['uid' => 2, 'doktype' => 98, 'nav_hide'=> 0];
                }
                return [];
            }
        };

        $menuRepository = $this->getMockBuilder(MenuRepository::class)
            ->onlyMethods(['populateAdditionalKeysForPage', 'isPageSuitableForLanguage'])
            ->setConstructorArgs([$context, $pageRepository, $this->createMock(EventDispatcherInterface::class)])
            ->getMock();
        $menuRepository->expects(self::any())->method('isPageSuitableForLanguage')->willReturn(true);
        $breadcrumbs = $menuRepository->getBreadcrumbsMenu($rootLine, ['excludeDoktypes' => 99]);
        self::assertSame(1, count($breadcrumbs));
    }
}
