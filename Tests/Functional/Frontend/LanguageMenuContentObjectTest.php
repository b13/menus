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

class LanguageMenuContentObjectTest extends FunctionalTestCase
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/translated_pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/language_menu_content_object_typoscript.csv');
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/'));
        $expected = '<a href="/" class="active">english</a><a href="/de/">german</a>';
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/language_menu_content_object_typoscript.csv');
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/de/'));
        $expected = '<a href="/">english</a><a href="/de/" class="active">german</a>';
        $body = (string)$response->getBody();
        self::assertStringContainsString($expected, $body);
    }
}
