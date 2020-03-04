<?php

namespace B13\Menus\Tests\Functional\DataProcessing;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


use B13\Menus\DataProcessing\TreeMenu;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;


class TreeMenuProcessorTest extends DataProcessingTest
{
    /**
     * @return array
     */
    public function setupDataProvider()
    {
        return [
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 3]]],
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
                                'isInRootLine' => true,
                                'isCurrentPage' => false
                            ]
                        ]
                    ],
                    [
                        'uid' => 4,
                        'hasSubpages' => false,
                        'level' => 1,
                        'isInRootLine' => false,
                        'isCurrentPage' => false
                    ]
                ]
            ],
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 3]]],
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
                                'isCurrentPage' => true
                            ],
                            [
                                'uid' => 4,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false
                            ]
                        ]
                    ]
                ]
            ],
            [
                'tsfe' => ['id' => 2, 'rootLine' => [['uid' => 1], ['uid' => 2], ['uid' => 3]]],
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
                                        'isInRootLine' => true,
                                        'isCurrentPage' => false
                                    ]
                                ]
                            ],
                            [
                                'uid' => 4,
                                'hasSubpages' => false,
                                'level' => 2,
                                'isInRootLine' => false,
                                'isCurrentPage' => false
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider setupDataProvider
     */
    public function processTest(array $tsfe, array $configuration, array $expected): void
    {
        $site = GeneralUtility::makeInstance(NullSite::class);
        $request = GeneralUtility::makeInstance(ServerRequest::class);
        $request = $request->withAttribute('site', $site);
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(TypoScriptFrontendController::class, null, $site, $site->getLanguageById(0));
        $GLOBALS['TSFE']->rootLine = $tsfe['rootLine'];
        $GLOBALS['TSFE']->id = $tsfe['id'];

        $treeMenuProccessor = GeneralUtility::makeInstance(TreeMenu::class);
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $treeMenu = $treeMenuProccessor->process($contentObjectRenderer, [], $configuration, []);
        $this->reduceResultsRecursive($treeMenu['my-tree']);
        $this->assertSame($expected, $treeMenu['my-tree']);
    }
}
