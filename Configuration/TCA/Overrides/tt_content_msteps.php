<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:msteps',
        'msteps',
        'content-carousel-item-textandimage',
    ],
    'image',
    'after'
);
$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['msteps'] = 'content-carousel-item-textandimage';

$GLOBALS['TCA']['tt_content']['palettes']['msteps'] = [
    'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:msteps.palette',
    'showitem' => 'CType,--linebreak--,header,--linebreak--,tx_ximatypo3manual_children',
];

$GLOBALS['TCA']['tt_content']['types']['msteps'] = [
    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;msteps,
                --div--;LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:tab.manual_relations,
                    --palette--;;manual-relations,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,colPos',
    'columnsOverrides' => [
        'tx_ximatypo3manual_children' => [
            'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:msteps.elements',
        ],
    ],
];
