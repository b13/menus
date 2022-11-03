<?php

declare(strict_types=1);

namespace B13\Menus\Tests\Functional\DataProcessing;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class DataProcessingTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/menus'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/menus/Tests/Functional/Fixtures/pages.csv');
    }

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

    protected function reduceResultsRecursive(array &$results): void
    {
        foreach ($results as &$result) {
            if (!empty($result['subpages'])) {
                $this->reduceResultsRecursive($result['subpages']);
            }
        }
        $results = $this->reduceResults($results);
    }

    protected function getTypoScriptFrontendController(Site $site, int $pageId): TypoScriptFrontendController
    {
        if ((new Typo3Version())->getMajorVersion() < 11) {
            return GeneralUtility::makeInstance(TypoScriptFrontendController::class, null, $site, $site->getLanguageById(0));
        }
        $context = $this->getMockBuilder(Context::class)
            ->getMock();
        $context->expects(self::any())->method('hasAspect')->with('frontend.preview')->willReturn(false);
        $context->expects(self::any())->method('setAspect');
        $siteLanguage = $this->getMockBuilder(SiteLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $siteLanguage->expects(self::any())->method('getTypo3Language')->willReturn('default');
        $pageArguments = $this->getMockBuilder(PageArguments::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageArguments->expects(self::any())->method('getPageId')->willReturn($pageId);
        $pageArguments->expects(self::any())->method('getPageType')->willReturn('0');
        $pageArguments->expects(self::any())->method('getArguments')->willReturn([]);
        $frontendUserAuth = $this->getMockBuilder(FrontendUserAuthentication::class)
            ->disableOriginalConstructor()
            ->getMock();
        $controller = $this->getAccessibleMock(
            TypoScriptFrontendController::class,
            ['get_cache_timeout'],
            [
                $context,
                $site,
                $siteLanguage,
                $pageArguments,
                $frontendUserAuth,
            ]
        );
        $controller->expects(self::any())->method('get_cache_timeout')->willReturn(1);
        return $controller;
    }
}
