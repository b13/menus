<?php

namespace B13\Menus\Tests\Functional\Functional;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractFrontendTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/menus'];
    protected array $coreExtensionsToLoad = ['core', 'frontend'];
    protected array $pathsToLinkInTestInstance = ['typo3conf/ext/menus/Build/sites' => 'typo3conf/sites'];

    protected function executeFrontendRequestWrapper(InternalRequest $request, InternalRequestContext $context = null, bool $followRedirects = false): ResponseInterface
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 11) {
            return $this->executeFrontendRequest($request, $context, $followRedirects);
        }
        return $this->executeFrontendSubRequest($request, $context, $followRedirects);
    }
}
