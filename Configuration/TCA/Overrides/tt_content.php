<?php


declare(strict_types=1);
defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tt_content',
    [
        'tx_menus_anchor_nav_title' => [
            'exclude' => 0,
            'label' => 'Nav Title',
            'config' => [
                'type' => 'input',
                'renderType' => 'input',
                'size' => 50,
                'max' => 100,
                'eval' => 'trim',
            ],
        ],
        'tx_menus_show_in_anchor_menu' => [
            'label' => 'Show in anchor menu',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
    ],
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'tx_menus_anchor_nav_title',
    'header',
    'after:header'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'tx_menus_show_in_anchor_menu',
    'header',
    'after:header'
);



