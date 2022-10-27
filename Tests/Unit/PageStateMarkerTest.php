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
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->rootLine = [
            ['uid' => 1],
            ['uid' => 2],
        ];
        $GLOBALS['TSFE']->id = 2;
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
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->rootLine = [
            ['uid' => 1],
            ['uid' => 2],
            ['uid' => 3],
        ];
        $GLOBALS['TSFE']->id = 3;
        PageStateMarker::markStatesRecursively($page, 1);
        self::assertTrue($page['isInRootLine']);
        self::assertTrue($page['subpages'][0]['isInRootLine']);
        self::assertTrue($page['subpages'][0]['subpages'][0]['isInRootLine']);
    }
}
