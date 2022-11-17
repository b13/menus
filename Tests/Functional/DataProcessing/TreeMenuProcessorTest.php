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

use B13\Menus\DataProcessing\TreeMenu;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class TreeMenuProcessorTest extends DataProcessingTest
{
    /**
     * @return array
     */
    public function setupDataProvider()
    {
        return [
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => ['as' => 'my-tree', 'entryPoints' => 1, 'depth' => 2],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => true,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                        'subpages' => [
                            [
                                'uid' => 3,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                        ],
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => ['as' => 'my-tree', 'entryPoints' => 1, 'depth' => 0, 'includeRootPages' => true],
                'expected' => [
                    [
                        'uid' => 1,
                        'hasSubpages' => true,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                        'subpages' => [
                            [
                                'uid' => 2,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => true,
                                'isCurrentPage' => true,
                            ],
                            [
                                'uid' => 4,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => ['as' => 'my-tree', 'entryPoints' => 1, 'depth' => 2, 'includeRootPages' => true],
                'expected' => [
                    [
                        'uid' => 1,
                        'hasSubpages' => true,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                        'subpages' => [
                            [
                                'uid' => 2,
                                'hasSubpages' => true,
                                'level' => 2,
                                'isInRootLine' => true,
                                'isCurrentPage' => true,
                                'subpages' => [
                                    [
                                        'uid' => 3,
                                        'hasSubpages' => false,
                                        'level' => 3,
                                        'isInRootLine' => false,
                                        'isCurrentPage' => false,
                                    ],
                                ],
                            ],
                            [
                                'uid' => 4,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 5, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 5]]],
                'configuration' => ['as' => 'my-tree', 'entryPoints' => 1, 'depth' => 2],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => true,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                        'subpages' => [
                            [
                                'uid' => 3,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                        ],
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            // includeNotInMenu option tests
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => ['as' => 'my-tree', 'entryPoints' => 1, 'depth' => 2, 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => true,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                        'subpages' => [
                            [
                                'uid' => 3,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                            [
                                'uid' => 5,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                        ],
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 6,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => ['as' => 'my-tree', 'entryPoints' => 1, 'depth' => 0, 'includeRootPages' => true, 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 1,
                        'hasSubpages' => true,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                        'subpages' => [
                            [
                                'uid' => 2,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => true,
                                'isCurrentPage' => true,
                            ],
                            [
                                'uid' => 4,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                            [
                                'uid' => 6,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => ['as' => 'my-tree', 'entryPoints' => 1, 'depth' => 2, 'includeRootPages' => true, 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 1,
                        'hasSubpages' => true,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                        'subpages' => [
                            [
                                'uid' => 2,
                                'hasSubpages' => true,
                                'level' => 2,
                                'isInRootLine' => true,
                                'isCurrentPage' => true,
                                'subpages' => [
                                    [
                                        'uid' => 3,
                                        'hasSubpages' => false,
                                        'level' => 3,
                                        'isInRootLine' => false,
                                        'isCurrentPage' => false,
                                    ],
                                    [
                                        'uid' => 5,
                                        'hasSubpages' => false,
                                        'level' => 3,
                                        'isInRootLine' => false,
                                        'isCurrentPage' => false,
                                    ],
                                ],
                            ],
                            [
                                'uid' => 4,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                            [
                                'uid' => 6,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 5, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 5]]],
                'configuration' => ['as' => 'my-tree', 'entryPoints' => 1, 'depth' => 2, 'includeRootPages' => true, 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 1,
                        'hasSubpages' => true,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                        'subpages' => [
                            [
                                'uid' => 2,
                                'hasSubpages' => true,
                                'level' => 2,
                                'isInRootLine' => true,
                                'isCurrentPage' => false,
                                'subpages' => [
                                    [
                                        'uid' => 3,
                                        'hasSubpages' => false,
                                        'level' => 3,
                                        'isInRootLine' => false,
                                        'isCurrentPage' => false,
                                    ],
                                    [
                                        'uid' => 5,
                                        'hasSubpages' => false,
                                        'level' => 3,
                                        'isInRootLine' => true,
                                        'isCurrentPage' => true,
                                    ],
                                ],
                            ],
                            [
                                'uid' => 4,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                            [
                                'uid' => 6,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 6, 'rootLine' => [['uid' => 1], ['uid' => 6]]],
                'configuration' => ['as' => 'my-tree', 'entryPoints' => 1, 'depth' => 2, 'includeRootPages' => true, 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 1,
                        'hasSubpages' => true,
                        'level' => 1,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                        'subpages' => [
                            [
                                'uid' => 2,
                                'hasSubpages' => true,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                                'subpages' => [
                                    [
                                        'uid' => 3,
                                        'hasSubpages' => false,
                                        'level' => 3,
                                        'isInRootLine' => false,
                                        'isCurrentPage' => false,
                                    ],
                                    [
                                        'uid' => 5,
                                        'hasSubpages' => false,
                                        'level' => 3,
                                        'isInRootLine' => false,
                                        'isCurrentPage' => false,
                                    ],
                                ],
                            ],
                            [
                                'uid' => 4,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false,
                            ],
                            [
                                'uid' => 6,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => true,
                                'isCurrentPage' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider setupDataProvider
     */
    public function processTest(array $tsfe, array $configuration, array $expected): void
    {
        $site = GeneralUtility::makeInstance(Site::class, 'main', $tsfe['id'], []);
        $request = GeneralUtility::makeInstance(ServerRequest::class);
        $request = $request->withAttribute('site', $site);
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $GLOBALS['TSFE'] = $this->getTypoScriptFrontendController($site, $tsfe['id']);
        $GLOBALS['TSFE']->rootLine = $tsfe['rootLine'];
        $GLOBALS['TSFE']->id = $tsfe['id'];

        $treeMenuProccessor = GeneralUtility::makeInstance(TreeMenu::class);
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $treeMenu = $treeMenuProccessor->process($contentObjectRenderer, [], $configuration, []);
        $this->reduceResultsRecursive($treeMenu['my-tree']);
        self::assertSame($expected, $treeMenu['my-tree']);
    }

    /**
     * @return array
     */
    public function cacheDataProvider()
    {
        return [
            // entry point 2
            [
                'tsfe' => ['id' => 1, 'rootLine' => [['uid' => 1]]],
                'entryPoints' => 2,
                'expectedTags' => ['menuId_2'],
            ],
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'entryPoints' => 2,
                'expectedTags' => ['menuId_2'],
            ],
            [
                'tsfe' => ['id' => 3, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 3]]],
                'entryPoints' => 2,
                'expectedTags' => ['menuId_2'],
            ],
            // entry point 1
            [
                'tsfe' => ['id' => 1, 'rootLine' => [['uid' => 1]]],
                'entryPoints' => 1,
                'expectedTags' => ['menuId_1', 'menuId_2'],
            ],
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'entryPoints' => 1,
                'expectedTags' => ['menuId_1', 'menuId_2'],
            ],
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 3]]],
                'entryPoints' => 1,
                'expectedTags' => ['menuId_1', 'menuId_2'],
            ],
            // menuId_3 and menuId_4 are never added to tags, because they are leaves
        ];
    }

    /**
     * @test
     * @dataProvider cacheDataProvider
     */
    public function menuIdTagsAreAddedToPageCache(array $tsfe, int $entryPoints, array $expectedTags): void
    {
        $site = GeneralUtility::makeInstance(Site::class, 'main', $tsfe['id'], []);
        $request = GeneralUtility::makeInstance(ServerRequest::class);
        $request = $request->withAttribute('site', $site);
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $GLOBALS['TSFE'] = $this->getTypoScriptFrontendController($site, $tsfe['id']);
        $GLOBALS['TSFE']->rootLine = $tsfe['rootLine'];
        $GLOBALS['TSFE']->id = $tsfe['id'];
        $configuration = ['as' => 'my-tree', 'entryPoints' => $entryPoints, 'depth' => 2];

        $treeMenuProccessor = GeneralUtility::makeInstance(TreeMenu::class);
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $treeMenuProccessor->process($contentObjectRenderer, [], $configuration, []);
        $pageCacheTags = $GLOBALS['TSFE']->getPageCacheTags();
        self::assertSame(count($expectedTags), count($pageCacheTags));
        foreach ($expectedTags as $expectedTag) {
            self::assertTrue(in_array($expectedTag, $pageCacheTags, true));
        }
    }
}
