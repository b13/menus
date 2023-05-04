<?php

namespace B13\Menus\Tests\Functional\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class DataHandlerTest extends FunctionalTestCase
{
    protected DataHandler $dataHandler;
    protected BackendUserAuthentication $backendUser;

    protected array $testExtensionsToLoad = ['typo3conf/ext/menus'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/caches.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->backendUser = $GLOBALS['BE_USER'] = $this->setUpBackendUser(1);
        Bootstrap::initializeLanguageObject();
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }

    public function cmdmapDataProvider(): array
    {
        return [
            'copy page' => ['cmdmap' => ['pages' => [
                3 => [
                    'copy' => 2,
                ],
            ]]],
            'move page into' => ['cmdmap' => ['pages' => [
                3 => [
                    'move' => 2,
                ],
            ]]],
            'move page after' => ['cmdmap' => ['pages' => [
                3 => [
                    'move' => -2,
                ],
            ]]],
        ];
    }

    /**
     * @test
     */
    public function editPageRemovesPageCacheWhereMenuIsUsed(): void
    {
        $datamap = [
            'pages' => [
                2 => ['title' => 'foo'],
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->assertCacheIsEmpty();
    }

    /**
     * @test
     * @dataProvider cmdmapDataProvider
     */
    public function pageCacheIsClearedAfterCmdmap(array $cmdmap): void
    {
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $this->assertCacheIsEmpty();
    }

    protected function assertCacheIsEmpty(): void
    {
        if ((new Typo3Version())->getMajorVersion() < 10) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('cache_pages');
        } else {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        }
        $rows = $queryBuilder->select('*')
            ->from('cache_pages')
            ->execute()
            ->fetchAllAssociative();
        self::assertSame(0, count($rows));
    }
}
