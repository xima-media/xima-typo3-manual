<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang_db.xlf:tx_ximatypo3manual_domain_model_term',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'languageField' => 'sys_language_uid',
        'translationSource' => 'l10n_source',
        'origUid' => 't3_origuid',
        'delete' => 'deleted',
        'default_sortby' => 'title ASC',
        'groupName' => 'content',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:xima_typo3_manual/Resources/Public/Icons/glossary-term.svg',
        'searchFields' => 'title, description',
    ],
    'types' => [
        '0' => [
            'showitem' => '
         --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            title, synonyms, description, link,
         --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,
            hidden,
      ',
        ],
    ],
    'columns' => [

        'title' => [
            'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang_db.xlf:tx_ximatypo3manual_domain_model_term.title',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 255,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'synonyms' => [
            'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang_db.xlf:tx_ximatypo3manual_domain_model_term.synonyms',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'eval' => 'trim',
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang_db.xlf:tx_ximatypo3manual_domain_model_term.description',
            'config' => [
                'type' => 'text',
                'rows' => 8,
                'cols' => 40,
                'max' => 1000,
                'eval' => 'trim',
            ],
        ],
        'link' => [
            'exclude' => true,
            'label' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang_db.xlf:tx_ximatypo3manual_domain_model_term.link',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputLink',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim',
                'softref' => 'typolink',
            ],
        ],
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => '',
                        'value' => 0,
                    ],
                ],
                'foreign_table' => 'tx_ximatypo3manual_domain_model_term',
                'foreign_table_where' =>
                    'AND {#tx_ximatypo3manual_domain_model_term}.{#pid}=###CURRENT_PID###'
                    . ' AND {#tx_ximatypo3manual_domain_model_term}.{#sys_language_uid} IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_source' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
            ],
        ],
        't3ver_label' => [
            'displayCond' => 'FIELD:t3ver_label:REQ:true',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'none',
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
    ],
];
