<?php

namespace B13\Menus\Tests\Functional\DataProcessing;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Prophecy\Argument;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
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

    protected function getTypoScriptFrontendController(SiteInterface $site, int $pageId): TypoScriptFrontendController
    {
        if ((new Typo3Version())->getMajorVersion() < 11) {
            return GeneralUtility::makeInstance(TypoScriptFrontendController::class, null, $site, $site->getLanguageById(0));
        }
        $context = $this->prophesize(Context::class);
        $context->hasAspect('frontend.preview')->willReturn(false);
        $context->setAspect('frontend.preview', Argument::any());
        $siteLanguage = $this->prophesize(SiteLanguage::class);
        $siteLanguage->getTypo3Language()->willReturn('default');
        $pageArguments = $this->prophesize(PageArguments::class);
        $pageArguments->getPageid()->willReturn($pageId);
        $pageArguments->getPageType()->willReturn(0);
        $pageArguments->getArguments()->willReturn([]);
        $frontendUserAuth = $this->prophesize(FrontendUserAuthentication::class);

        $controller = $this->getAccessibleMock(
            TypoScriptFrontendController::class,
            ['get_cache_timeout'],
            [
                $context->reveal(),
                $site,
                $siteLanguage->reveal(),
                $pageArguments->reveal(),
                $frontendUserAuth->reveal()
            ]
        );
        $controller->expects(self::any())->method('get_cache_timeout')->willReturn(1);
        return $controller;
    }
}
