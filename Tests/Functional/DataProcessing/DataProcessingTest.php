<?php

namespace B13\Menus\Tests\Functional\DataProcessing;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


use B13\Menus\DataProcessing\BreadcrumbsMenu;
use B13\Menus\Domain\Repository\MenuRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;


abstract class DataProcessingTest extends FunctionalTestCase
{

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/menus'
    ];

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/menus/Tests/Functional/Fixtures/pages.xml');
    }

    /**
     * @param array $results
     * @return array
     */
    protected function reduceResults(array $results): array
    {
        $reduced = [];
        $keys = ['uid', 'hasSubpages', 'level', 'isInRootLine', 'isCurrentPage'];
        foreach ($results as $result) {
            $reduce = array_intersect_key($result, array_flip($keys));
            if (!empty($result['subpages'])) {
                $reduce['subpages'] = $result['subpages'];
            }
            $reduced[] = $reduce;
        }
        return $reduced;
    }

    /**
     * @param array $results
     * @return array
     */
    protected function reduceResultsRecursive(array &$results): void
    {
        foreach ($results as &$result) {
            if (!empty($result['subpages'])) {
                $this->reduceResultsRecursive($result['subpages']);
            }
        }
        $results = $this->reduceResults($results);
    }
}
