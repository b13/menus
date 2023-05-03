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

class LanguageMenuFluidTest extends AbstractFrontendTest
{
    /**
     * @test
     */
    public function menuOnRootPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/translated_pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/language_menu_fluid_typoscript.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/'));
        $expected = '<a class="active" href="/">english</a><a href="/de/">german</a>';
        $body = (string)$response->getBody();
        self::assertStringContainsString($expected, $body);
    }

    /**
     * @test
     */
    public function menuOnSubpage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/translated_pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/language_menu_fluid_typoscript.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/de/'));
        $expected = '<a href="/">english</a><a class="active" href="/de/">german</a>';
        $body = (string)$response->getBody();
        self::assertStringContainsString($expected, $body);
    }
}
