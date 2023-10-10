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
    'showitem' => 'layout,--linebreak--,bodytext',
];

$GLOBALS['TCA']['tt_content']['types']['mbox'] = [
    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;mbox,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,colPos',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => false,
            ],
        ],
        'layout' => [

        ],
    ],
];
