<?php

namespace B13\Menus\Tests\Functional\Functional;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ListMenuContentObjectTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/menus'];
    protected $coreExtensionsToLoad = ['core', 'frontend'];
    protected $pathsToLinkInTestInstance = ['typo3conf/ext/menus/Build/sites' => 'typo3conf/sites'];

    /**
     * @test
     */
    public function menuOnRootPage(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/menus/Tests/Functional/Frontend/Fixtures/pages.csv');
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/menus/Tests/Functional/Frontend/Fixtures/list_menu_content_object_typoscript.csv');
        $response = $this->executeFrontendRequest(new InternalRequest('http://localhost/'));
        $expected = '<a href="/page-1">page-1</a>';
        $body = (string)$response->getBody();
        self::assertStringContainsString($expected, $body);
    }

    /**
     * @test
     */
    public function menuOnSubpage(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/menus/Tests/Functional/Frontend/Fixtures/pages.csv');
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/menus/Tests/Functional/Frontend/Fixtures/list_menu_content_object_typoscript.csv');
        $response = $this->executeFrontendRequest(new InternalRequest('http://localhost/page-1'));
        $expected = '<a href="/page-1">page-1</a>';
        $body = (string)$response->getBody();
        self::assertStringContainsString($expected, $body);
    }
}
