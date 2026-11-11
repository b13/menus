<?php

declare(strict_types=1);

namespace B13\Menus\Tests\Functional\Frontend;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class TreeMenuCacheTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/menus'];
    protected array $coreExtensionsToLoad = ['core', 'frontend'];
    protected array $pathsToLinkInTestInstance = ['typo3conf/ext/menus/Build/sites' => 'typo3conf/sites'];

    protected array $configurationToUseInTestInstance = [
        'SYS' => [
            'caching' => [
                'cacheConfigurations' => [
                    'hash' => [
                        'backend' => Typo3DatabaseBackend::class,
                    ],
                ],
            ],
        ],
    ];

    public static function previouslyHiddenPageDataProvider(): array
    {
        return [
            'first level page whose visible siblings all have subpages' => [2, 'page-a'],
            'only subpage of a page' => [5, 'page-b-1'],
        ];
    }

    #[Test]
    #[DataProvider('previouslyHiddenPageDataProvider')]
    public function previouslyHiddenPageIsShownAfterNavHideIsRemoved(int $pageId, string $title): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MenuCache/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MenuCache/tree_menu_typoscript.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->get(ConnectionPool::class)->getConnectionForTable('pages')->update('pages', ['nav_hide' => 1], ['uid' => $pageId]);

        $body = (string)$this->executeFrontendSubRequest(new InternalRequest('http://localhost/'))->getBody();
        self::assertStringNotContainsString('<li>' . $title . '</li>', $body);

        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
        $dataHandler = $this->get(DataHandler::class);
        $dataHandler->start(['pages' => [$pageId => ['nav_hide' => 0]]], [], $backendUser);
        $dataHandler->process_datamap();

        $body = (string)$this->executeFrontendSubRequest(new InternalRequest('http://localhost/'))->getBody();
        self::assertStringContainsString('<li>' . $title . '</li>', $body);
    }
}
