<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'Box',
        'mbox',
        'content-idea',
    ],
    'image',
    'after'
);
$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['mbox'] = 'content-idea';

$GLOBALS['TCA']['tt_content']['palettes']['mbox'] = [
    'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:mbox.palette',
    'showitem' => 'CType,--linebreak--,header,--linebreak--,layout,--linebreak--,bodytext',
];

$GLOBALS['TCA']['tt_content']['types']['mbox'] = [
    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;mbox,
                --div--;LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:tab.manual_relations,
                    --palette--;;manual-relations,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,colPos',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => false,
                'required' => true,
            ],
        ],
        'layout' => [
            'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:mbox.layout.label',
            'config' => [
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => false,
                    ],
                ],
                'items' => [
                    [
                        'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:mbox.layout.default',
                        'icon' => 'EXT:xima_typo3_manual/Resources/Public/Icons/icon-default.svg',
                        'value' => 0,
                    ],
                    [
                        'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:mbox.layout.info',
                        'icon' => 'EXT:xima_typo3_manual/Resources/Public/Icons/icon-info.svg',
                        'value' => 1,
                    ],
                    [
                        'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:mbox.layout.success',
                        'icon' => 'EXT:xima_typo3_manual/Resources/Public/Icons/icon-success.svg',
                        'value' => 2,
                    ],
                    [
                        'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:mbox.layout.warning',
                        'icon' => 'EXT:xima_typo3_manual/Resources/Public/Icons/icon-warning.svg',
                        'value' => 3,
                    ],
                    [
                        'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:mbox.layout.error',
                        'icon' => 'EXT:xima_typo3_manual/Resources/Public/Icons/icon-error.svg',
                        'value' => 4,
                    ],
                ],
            ],
        ],
    ],
];
