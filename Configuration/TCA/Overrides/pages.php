<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\UserFunctions\SelectItemsProcFunc;

defined('TYPO3') || die();

ExtensionManagementUtility::registerPageTSConfigFile(
    'xima_typo3_manual',
    'Configuration/TSconfig/Page.tsconfig',
    'XIMA Manual'
);

ExtensionManagementUtility::addTcaSelectItem(
    'pages',
    'doktype',
    [
        'label' => Configuration::LANGUAGE_FILE . 'manual_page_type',
        'value' => Configuration::DOKTYPE_MANUAL,
        'icon' => 'EXT:xima_typo3_manual/Resources/Public/Icons/apps-pagetree-manual.svg',
        'group' => 'default',
    ],
    '1',
    'after'
);

ExtensionManagementUtility::addTCAcolumns('pages', [
    'tx_ximatypo3manual_relations' => [
        'exclude' => true,
        'label' => Configuration::LANGUAGE_FILE . 'tx_ximatypo3manual_relation',
        'description' => Configuration::LANGUAGE_FILE . 'tx_ximatypo3manual_relation.description',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectCheckBox',
            'items' => [],
            'appearance' => [
                'expandAll' => true,
            ],
            'itemsProcFunc' => SelectItemsProcFunc::class . '->getItems',
        ],
    ],
]);

$GLOBALS['TCA']['pages']['palettes']['manual-relations'] = [
    'label' => Configuration::LANGUAGE_FILE . 'palettes.manual_relations',
    'showitem' => 'tx_ximatypo3manual_relations',
];

ArrayUtility::mergeRecursiveWithOverrule(
    $GLOBALS['TCA']['pages'],
    [
        'ctrl' => [
            'typeicon_classes' => [
                Configuration::DOKTYPE_MANUAL => 'apps-pagetree-manual',
                Configuration::DOKTYPE_MANUAL . '-contentFromPid' => 'apps-pagetree-manual-contentFromPid',
                Configuration::DOKTYPE_MANUAL . '-root' => 'apps-pagetree-manual-root',
                Configuration::DOKTYPE_MANUAL . '-hideinmenu' => 'apps-pagetree-manual-hideinmenu',
            ],
        ],
        'types' => [
            Configuration::DOKTYPE_MANUAL => [
                // Honoured by TYPO3 v14, ignored by v13 where ext_localconf.php feeds the PageDoktypeRegistry instead
                'allowedRecordTypes' => Configuration::ALLOWED_RECORD_TYPES,
                'showitem' => '
                    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                        --palette--;;standard,
                        --palette--;;title,
                    --div--;' . Configuration::LANGUAGE_FILE . 'tab.manual_relations,
                        --palette--;;manual-relations,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.resources,
                        --palette--;;media,
                        --palette--;;config,is_siteroot,
                    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,
                        --palette--;;visibility,',
            ],
        ],
    ]
);
