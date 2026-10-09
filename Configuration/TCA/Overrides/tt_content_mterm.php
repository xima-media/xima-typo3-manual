<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Xima\XimaTypo3Manual\Configuration;

defined('TYPO3') || die();

ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => Configuration::LANGUAGE_FILE . 'wizard.mterm.title',
        'value' => 'mterm',
        'icon' => 'content-text',
        'group' => 'default',
        'description' => Configuration::LANGUAGE_FILE . 'wizard.mterm.description',
    ],
    'image',
    'after'
);
$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['mterm'] = 'content-text';

$GLOBALS['TCA']['tt_content']['palettes']['mterm'] = [
    'label' => Configuration::LANGUAGE_FILE . 'mterm.palette',
    'showitem' => 'CType,--linebreak--,header,--linebreak--,bodytext',
];

$GLOBALS['TCA']['tt_content']['types']['mterm'] = [
    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;mterm,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,colPos',
    'columnsOverrides' => [
        'header' => [
            'label' => Configuration::LANGUAGE_FILE . 'mterm.term',
            'config' => [
                'required' => true,
            ],
        ],
        'bodytext' => [
            'label' => Configuration::LANGUAGE_FILE . 'mterm.definition',
            'config' => [
                'enableRichtext' => true,
                'richtextConfiguration' => 'xima_typo3_manual',
                'required' => true,
            ],
        ],
    ],
];
