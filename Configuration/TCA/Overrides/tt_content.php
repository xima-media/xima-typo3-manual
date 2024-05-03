<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$tempFields = [
    'tx_ximatypo3manual_relations' => [
        'exclude' => true,
        'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:tx_ximatypo3manual_relation',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectCheckBox',
            'items' => [],
            'appearance' => [
                'expandAll' => true,
            ],
            'itemsProcFunc' => \Xima\XimaTypo3Manual\UserFunctions\SelectItemsProcFunc::class . '->getItems',
        ],
    ],
    'tx_ximatypo3manual_children' => [
        'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:tx_ximatypo3manual_children',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tt_content',
            'foreign_field' => 'tx_ximatypo3manual_parent',
            'foreign_sortby' => 'sorting',
            'appearance' => [
                'showSynchronizationLink' => true,
                'showAllLocalizationLink' => true,
                'showPossibleLocalizationRecords' => true,
            ],
            'overrideChildTca' => [
                'types' => [
                    'mtext' => [
                        'showitem' => '--palette--;;mtext,colPos',
                    ],
                ],
                'columns' => [
                    'CType' => [
                        'config' => [
                            'default' => 'mtext',
                            'items' => []
                        ],
                    ],
                    'colPos' => [
                        'config' => [
                            'default' => 1,
                            'items' => [],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

$GLOBALS['TCA']['tt_content']['palettes']['manual-relations'] = [
    'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:palettes.manual_relations',
    'showitem' => 'tx_ximatypo3manual_relations',
];

ExtensionManagementUtility::addTCAcolumns('tt_content', $tempFields);
