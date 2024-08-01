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
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class TreeMenuFluidTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/menus'];
    protected array $coreExtensionsToLoad = ['core', 'frontend'];
    protected array $pathsToLinkInTestInstance = ['typo3conf/ext/menus/Build/sites' => 'typo3conf/sites'];

    /**
     * @test
     */
    public function menuOnRootPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tree_menu_fluid_typoscript.csv');
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/'));
        $expected = '<a href="/page-1">page-1</a><a href="/page-2">page-2</a>';
        $body = (string)$response->getBody();
        self::assertStringContainsString($expected, $body);
    }

    /**
     * @test
     */
    public function menuOnSubpage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tree_menu_fluid_typoscript.csv');
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/page-1'));
        $expected = '<a class="active" href="/page-1">page-1</a><a href="/page-2">page-2</a>';
        $body = (string)$response->getBody();
        self::assertStringContainsString($expected, $body);
    }

    /**
     * @test
     */
    public function menuWithAccessRestrictionForNotLoggedinUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/access_restriction.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tree_menu_fluid_typoscript.csv');
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/'));
        $body = (string)$response->getBody();
        self::assertStringContainsString('<a href="/page-1">page-1</a>', $body);
        self::assertStringNotContainsString('<a href="/page-2">page-2</a>', $body);
    }

    /**
     * @test
     */
    public function menuWithAccessRestrictionForLoggedinUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/access_restriction.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tree_menu_fluid_typoscript.csv');
        $context = (new InternalRequestContext())->withFrontendUserId(1);
        $request = new InternalRequest('http://localhost/');
        $response = $this->executeFrontendSubRequest($request, $context);
        $body = (string)$response->getBody();
        self::assertStringContainsString('<a href="/page-1">page-1</a>', $body);
        self::assertStringContainsString('<a href="/page-2">page-2</a>', $body);
    }
}
