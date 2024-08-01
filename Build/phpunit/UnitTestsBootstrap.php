<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

call_user_func(function () {
    $testbase = new \TYPO3\TestingFramework\Core\Testbase();

    $testbase->defineSitePath();

    $requestType = \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_BE | \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_CLI;
    \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::run(0, $requestType);

    $testbase->createDirectory(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/typo3conf/ext');
    $testbase->createDirectory(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/typo3temp/assets');
    $testbase->createDirectory(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/typo3temp/var/tests');
    $testbase->createDirectory(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/typo3temp/var/transient');

    // Retrieve an instance of class loader and inject to core bootstrap
    $classLoader = require $testbase->getPackagesPath() . '/autoload.php';
    \TYPO3\CMS\Core\Core\Bootstrap::initializeClassLoader($classLoader);

    // Initialize default TYPO3_CONF_VARS
    $configurationManager = new \TYPO3\CMS\Core\Configuration\ConfigurationManager();
    $GLOBALS['TYPO3_CONF_VARS'] = $configurationManager->getDefaultConfiguration();

    $cache = new \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend(
        'core',
        new \TYPO3\CMS\Core\Cache\Backend\NullBackend('production', [])
    );
    // Set all packages to active
    $packageManager = \TYPO3\CMS\Core\Core\Bootstrap::createPackageManager(
        \TYPO3\CMS\Core\Package\UnitTestPackageManager::class,
        \TYPO3\CMS\Core\Core\Bootstrap::createPackageCache($cache)
    );
    \TYPO3\CMS\Core\Utility\GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Package\PackageManager::class, $packageManager);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::setPackageManager($packageManager);

    $testbase->dumpClassLoadingInformation();

    \TYPO3\CMS\Core\Utility\GeneralUtility::purgeInstances();
});
