<?php

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::registerPageTSConfigFile(
    'xima_typo3_manual',
    'Configuration/TSconfig/Page.tsconfig',
    'Xima Manual'
);

ExtensionManagementUtility::addTcaSelectItem(
    'pages',
    'doktype',
    [
        'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:manual_page_type',
        701,
        'EXT:xima_typo3_manual/Resources/Public/Icons/apps-pagetree-manual.svg',
    ],
    '1',
    'after'
);

ExtensionManagementUtility::addTCAcolumns(
    'pages',
    [
        'tx_ximatypo3manual_begroup' => [
            'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang_db.xlf:pages.tx_ximatypo3manual_begroup',
            'config' => [
                'foreign_table' => 'be_groups',
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 7,
                'maxitems' => 20,
            ],
        ],
    ]
);

ArrayUtility::mergeRecursiveWithOverrule(
    $GLOBALS['TCA']['pages'],
    [
        'ctrl' => [
            'typeicon_classes' => [
                701 => 'apps-pagetree-manual',
                '701-contentFromPid' => 'apps-pagetree-manual-contentFromPid',
                '701-root' => 'apps-pagetree-manual-root',
                '701-hideinmenu' => 'apps-pagetree-manual-hideinmenu',
            ],
        ],
        'types' => [
            701 => [
                'showitem' => '
                    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                        --palette--;;standard,
                        --palette--;;title,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.resources,
                        --palette--;;media,
                        --palette--;;config,is_siteroot,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,
                        --palette--;;visibility,
                        --palette--;;access_be,',
            ],
        ],
        'palettes' => [
            'access_be' => [
                'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang_db.xlf:pages.palettes.access',
                'showitem' => 'tx_ximatypo3manual_begroup',
            ]
        ]
    ]
);
