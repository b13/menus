<?php

namespace B13\Menus\Tests\Unit\DataProcessing;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


use B13\Menus\DataProcessing\BreadcrumbsMenu;
use B13\Menus\Domain\Repository\MenuRepository;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;


class BreadcrumbsMenuTest extends UnitTestCase
{

    /**
     * @test
     */
    public function processMarksPageStates(): void
    {
        $pages = [
            ['uid' => 1],
            ['uid' => 2]

        ];
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->rootLine = [
            ['uid' => 1],
            ['uid' => 2]
        ];
        $GLOBALS['TSFE']->id = 2;
        $menuRepository = $this->prophesize(MenuRepository::class);
        $menuRepository->getBreadcrumbsMenu($GLOBALS['TSFE']->rootLine, [])->willReturn($pages);
        $contentDataProcessor = $this->prophesize(ContentDataProcessor::class);
        $contentObjectRenderer = $this->prophesize(ContentObjectRenderer::class);
        $breadcrumbsMenuDataProcessor = $this->getMockBuilder(BreadcrumbsMenu::class)
            ->setMethods(['processAdditionalDataProcessors'])
            ->setConstructorArgs([$contentDataProcessor->reveal(), $menuRepository->reveal()])
            ->getMock();
        $contentObjectRenderer->stdWrapValue('as', [], 'breadcrumbs')->willReturn('breadcrumbs');
        $processedData = $breadcrumbsMenuDataProcessor->process($contentObjectRenderer->reveal(), [], [], []);
        $this->assertTrue($processedData['breadcrumbs'][0]['isInRootLine']);
        $this->assertTrue($processedData['breadcrumbs'][1]['isInRootLine']);
        $this->assertFalse($processedData['breadcrumbs'][0]['isCurrentPage']);
        $this->assertTrue($processedData['breadcrumbs'][1]['isCurrentPage']);
    }
}
