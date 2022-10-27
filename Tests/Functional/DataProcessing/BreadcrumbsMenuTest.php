<?php

declare(strict_types=1);

namespace B13\Menus\Tests\Functional\DataProcessing;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Menus\DataProcessing\BreadcrumbsMenu;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class BreadcrumbsMenuTest extends DataProcessingTest
{
    /**
     * @return array
     */
    public function setupDataProvider()
    {
        return [
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => [],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'level' => 2,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 1,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 3, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 3]]],
                'configuration' => ['excludeDoktypes' => 99],
                'expected' => [
                    [
                        'uid' => 3,
                        'hasSubpages' => false,
                        'level' => 2,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 1,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => ['excludePages' => 1],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 5, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 5]]],
                'configuration' => [],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'level' => 2,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 1,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 5, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 5]]],
                'configuration' => ['includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 5,
                        'hasSubpages' => false,
                        'level' => 3,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'level' => 2,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 1,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 5, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 5]]],
                'configuration' => ['includeNotInMenu' => 1, 'excludePages' => '1'],
                'expected' => [
                    [
                        'uid' => 5,
                        'hasSubpages' => false,
                        'level' => 2,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 5, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 5]]],
                'configuration' => ['includeNotInMenu' => 1, 'excludeDoktypes' => 99],
                'expected' => [
                    [
                        'uid' => 5,
                        'hasSubpages' => false,
                        'level' => 2,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 1,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 5, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 5]]],
                'configuration' => ['includeNotInMenu' => 1, 'excludeDoktypes' => 99, 'excludePages' => '1'],
                'expected' => [
                    [
                        'uid' => 5,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 5, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 5]]],
                'configuration' => ['excludeDoktypes' => 99, 'excludePages' => '1'],
                'expected' => [],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider setupDataProvider
     */
    public function processTest(array $tsfe, array $configuration, array $expected): void
    {
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->rootLine = $tsfe['rootLine'];
        $GLOBALS['TSFE']->id = $tsfe['id'];
        $breadcrumbsMenuProcessor = GeneralUtility::makeInstance(BreadcrumbsMenu::class);
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $breadcrumbs = $breadcrumbsMenuProcessor->process($contentObjectRenderer, [], $configuration, []);
        self::assertIsArray($breadcrumbs['breadcrumbs']);
        $reduced = $this->reduceResults($breadcrumbs['breadcrumbs']);
        self::assertSame($expected, $reduced);
    }
}
