<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addUserTSConfig(
    'options.pageTree.doktypesToShowInNewPageDragArea := addToList(701)'
);

$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['xima_typo3_manual'] = 'EXT:xima_typo3_manual/Configuration/RTE/Manual.yaml';

if (false === isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ximatypo3manual_glossarytermcache'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ximatypo3manual_glossarytermcache'] = [];
}
