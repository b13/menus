<?php

declare(strict_types=1);

namespace B13\Menus\Tests\Unit;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Menus\PageStateMarker;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageInformation;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PageStateMarkerTest extends UnitTestCase
{
    /**
     * @test
     */
    public function markStatesRecursiveMarksLevel2Pages(): void
    {
        $page = [
            'uid' => 1,
            'subpages' => [
                ['uid' => 2],
            ],
        ];
        $rootLine = [
            ['uid' => 1],
            ['uid' => 2],
        ];
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            $pageInformation = new PageInformation();
            $pageInformation->setRootLine($rootLine);
            $pageInformation->setId(2);
            $request = GeneralUtility::makeInstance(ServerRequest::class);
            $GLOBALS['TYPO3_REQUEST'] = $request->withAttribute('frontend.page.information', $pageInformation);
        } else {
            $GLOBALS['TSFE'] = new \stdClass();
            $GLOBALS['TSFE']->rootLine = $rootLine;
            $GLOBALS['TSFE']->id = 2;
        }
        PageStateMarker::markStatesRecursively($page, 1);
        self::assertTrue($page['isInRootLine']);
        self::assertTrue($page['subpages'][0]['isInRootLine']);
    }

    /**
     * @test
     */
    public function markStatesRecursiveMarksLevel3Pages(): void
    {
        $page = [
            'uid' => 1,
            'subpages' => [
                [
                    'uid' => 2,
                    'subpages' => [
                        [
                            'uid' => 3,
                        ],
                    ],
                ],
            ],
        ];

        $rootLine = [
            ['uid' => 1],
            ['uid' => 2],
            ['uid' => 3],
        ];
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            $pageInformation = new PageInformation();
            $pageInformation->setRootLine($rootLine);
            $pageInformation->setId(3);
            $request = GeneralUtility::makeInstance(ServerRequest::class);
            $GLOBALS['TYPO3_REQUEST'] = $request->withAttribute('frontend.page.information', $pageInformation);
        } else {
            $GLOBALS['TSFE'] = new \stdClass();
            $GLOBALS['TSFE']->rootLine = $rootLine;
            $GLOBALS['TSFE']->id = 3;
        }
        PageStateMarker::markStatesRecursively($page, 1);
        self::assertTrue($page['isInRootLine']);
        self::assertTrue($page['subpages'][0]['isInRootLine']);
        self::assertTrue($page['subpages'][0]['subpages'][0]['isInRootLine']);
    }
}
