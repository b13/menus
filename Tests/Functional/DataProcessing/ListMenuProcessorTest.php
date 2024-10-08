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

use B13\Menus\DataProcessing\ListMenu;
use TYPO3\CMS\Core\Cache\CacheDataCollector;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ListMenuProcessorTest extends DataProcessing
{
    /**
     * @return array
     */
    public static function setupDataProvider()
    {
        return [
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,6'],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 4, 'rootLine' => [['uid' => 1], ['uid' => 4]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,6'],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 4, 'rootLine' => [['uid' => 1], ['uid' => 4]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,3,5,6'],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 3,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 3, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 3]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,3,5,6'],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 3,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                ],
            ],
            // tests with includeNotInMenu
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,6', 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 6,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 4, 'rootLine' => [['uid' => 1], ['uid' => 4]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,6', 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 6,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 4, 'rootLine' => [['uid' => 1], ['uid' => 4]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,3,5,6', 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 3,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 5,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 6,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 3, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 3]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,3,5,6', 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 3,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 5,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 6,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 5, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 5]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,3,5,6', 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 3,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 5,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                    [
                        'uid' => 6,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                ],
            ],
            [
                'tsfe' => ['id' => 6, 'rootLine' => [['uid' => 1], ['uid' => 6]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,3,5,6', 'includeNotInMenu' => 1],
                'expected' => [
                    [
                        'uid' => 2,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 3,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 5,
                        'hasSubpages' => false,
                        'isInRootLine' => false,
                        'isCurrentPage' => false,
                    ],
                    [
                        'uid' => 6,
                        'hasSubpages' => false,
                        'isInRootLine' => true,
                        'isCurrentPage' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider setupDataProvider
     */
    public function processTest(array $tsfe, array $configuration, array $expected)
    {
        $site = GeneralUtility::makeInstance(Site::class, 'main', $tsfe['id'], []);
        $request = GeneralUtility::makeInstance(ServerRequest::class);
        $request = $request->withAttribute('site', $site);
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            $cacheDataCollector = new CacheDataCollector();
            $request = $request->withAttribute('frontend.cache.collector', $cacheDataCollector);
        }
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $GLOBALS['TSFE'] = $this->getTypoScriptFrontendController($site, $tsfe['id']);
        $GLOBALS['TSFE']->rootLine = $tsfe['rootLine'];
        $GLOBALS['TSFE']->id = $tsfe['id'];

        $listMenuProcessor = GeneralUtility::makeInstance(ListMenu::class);
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $listMenu = $listMenuProcessor->process($contentObjectRenderer, [], $configuration, []);

        self::assertIsArray($listMenu['my-list']);
        $this->reduceResultsRecursive($listMenu['my-list']);
        self::assertSame($expected, $listMenu['my-list']);
    }

    /**
     * @return array
     */
    public static function cacheDataProvider()
    {
        return [
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,6'],
                'expectedTags' => ['menuId_1'],
            ],
            [
                'tsfe' => ['id' => 4, 'rootLine' => [['uid' => 1], ['uid' => 4]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,6'],
                'expectedTags' => ['menuId_1'],
            ],
            [
                'tsfe' => ['id' => 4, 'rootLine' => [['uid' => 1], ['uid' => 4]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,3,5,6'],
                'expectedTags' => ['menuId_1', 'menuId_2'],
            ],
            [
                'tsfe' => ['id' => 3, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 3]]],
                'configuration' => ['as' => 'my-list', 'pages' => '2,4,3,5,6'],
                'expectedTags' => ['menuId_1', 'menuId_2'],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider cacheDataProvider
     */
    public function menuIdTagsAreAddedToPageCache(array $tsfe, array $configuration, array $expectedTags)
    {
        $site = GeneralUtility::makeInstance(Site::class, 'main', $tsfe['id'], []);
        $request = GeneralUtility::makeInstance(ServerRequest::class);
        $request = $request->withAttribute('site', $site);
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            $cacheDataCollector = new CacheDataCollector();
            $request = $request->withAttribute('frontend.cache.collector', $cacheDataCollector);
        }
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $GLOBALS['TSFE'] = $this->getTypoScriptFrontendController($site, $tsfe['id']);
        $GLOBALS['TSFE']->rootLine = $tsfe['rootLine'];
        $GLOBALS['TSFE']->id = $tsfe['id'];

        $listMenuProcessor = GeneralUtility::makeInstance(ListMenu::class);
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $listMenuProcessor->process($contentObjectRenderer, [], $configuration, []);
        $pageCacheTags = $GLOBALS['TSFE']->getPageCacheTags();

        self::assertSame(count($expectedTags), count($pageCacheTags));
        foreach ($expectedTags as $expectedTag) {
            self::assertTrue(in_array($expectedTag, $pageCacheTags, true));
        }
    }
}
