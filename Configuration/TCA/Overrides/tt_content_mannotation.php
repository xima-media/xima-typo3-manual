<?php

use Blueways\BwFocuspointImages\Preview\FocuspointPreviewRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'LLL:EXT:bw_focuspoint_images/Resources/Private/Language/locallang_db.xlf:tca.wizard.svg.title',
        'mannotation',
        'bw_focuspoint_images_svg',
    ],
    'image',
    'after'
);
$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['mannotation'] = 'bw_focuspoint_images_svg';

$GLOBALS['TCA']['tt_content']['palettes']['mannotation'] = [
    'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:mannotation.palette',
    'showitem' => 'CType,--linebreak--,header,--linebreak--,bodytext,--linebreak--,assets',
];

$GLOBALS['TCA']['tt_content']['types']['mannotation'] = [
    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;mannotation,
                --div--;LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:tab.manual_relations,
                    --palette--;;manual-relations,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
                'richtextConfiguration' => 'xima_typo3_manual',
            ],
        ],
        'assets' => [
            'config' => [
                'overrideChildTca' => [
                    'types' => [
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                            'showitem' => 'focus_points,--palette--;;filePalette',
                        ],
                    ],
                    'columns' => [
                        'uid_local' => [
                            'config' => [
                                'appearance' => [
                                    'elementBrowserAllowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'previewRenderer' => FocuspointPreviewRenderer::class,
];
