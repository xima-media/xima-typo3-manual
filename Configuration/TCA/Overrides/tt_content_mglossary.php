<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Xima\XimaTypo3Manual\Configuration;

defined('TYPO3') || die();

ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => Configuration::LANGUAGE_FILE . 'wizard.mglossary.title',
        'value' => 'mglossary',
        'icon' => 'content-special-indexed_search',
        'group' => 'default',
        'description' => Configuration::LANGUAGE_FILE . 'wizard.mglossary.description',
    ],
    'image',
    'after'
);
$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['mglossary'] = 'content-special-indexed_search';

$GLOBALS['TCA']['tt_content']['palettes']['mglossary'] = [
    'label' => Configuration::LANGUAGE_FILE . 'mglossary.palette',
    'showitem' => 'CType,--linebreak--,header,--linebreak--,tx_ximatypo3manual_children',
];

$GLOBALS['TCA']['tt_content']['types']['mglossary'] = [
    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;mglossary,
                --div--;' . Configuration::LANGUAGE_FILE . 'tab.manual_relations,
                    --palette--;;manual-relations,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,colPos',
    'columnsOverrides' => [
        'tx_ximatypo3manual_children' => [
            'label' => Configuration::LANGUAGE_FILE . 'mglossary.terms',
            'config' => [
                'overrideChildTca' => [
                    'columns' => [
                        'CType' => [
                            'config' => [
                                'default' => 'mterm',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
