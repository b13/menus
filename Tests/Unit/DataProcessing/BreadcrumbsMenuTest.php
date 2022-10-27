<?php

declare(strict_types=1);

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
            ['uid' => 2],

        ];
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->rootLine = [
            ['uid' => 1],
            ['uid' => 2],
        ];
        $GLOBALS['TSFE']->id = 2;
        $menuRepository = $this->getMockBuilder(MenuRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $menuRepository->expects(self::once())->method('getBreadcrumbsMenu')->with($GLOBALS['TSFE']->rootLine, [])->willReturn($pages);
        $contentDataProcessor = $this->getMockBuilder(ContentDataProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contentObjectRenderer = $this->getMockBuilder(ContentObjectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contentObjectRenderer->expects(self::once())->method('stdWrapValue')->with('as', [], 'breadcrumbs')->willReturn('breadcrumbs');
        $breadcrumbsMenuDataProcessor = $this->getMockBuilder(BreadcrumbsMenu::class)
            ->onlyMethods(['processAdditionalDataProcessors'])
            ->setConstructorArgs([$contentDataProcessor, $menuRepository])
            ->getMock();
        $processedData = $breadcrumbsMenuDataProcessor->process($contentObjectRenderer, [], [], []);
        self::assertTrue($processedData['breadcrumbs'][0]['isInRootLine']);
        self::assertTrue($processedData['breadcrumbs'][1]['isInRootLine']);
        self::assertFalse($processedData['breadcrumbs'][0]['isCurrentPage']);
        self::assertTrue($processedData['breadcrumbs'][1]['isCurrentPage']);
    }
}
