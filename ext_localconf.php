<?php

declare(strict_types=1);

use TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\IndexedSearch\Controller\SearchController;
use Xima\XimaTypo3Manual\Configuration;

defined('TYPO3') || die();

$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['xima_typo3_manual'] = 'EXT:xima_typo3_manual/Configuration/RTE/Manual.yaml';

// Sizes module-body iframes and backend content previews (e.g. the icon picker's inline images, otherwise unstyled
// outside the RTE). TYPO3 v13's module toolbar has a different height, so it layers a small override on top.
$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['xima_typo3_manual'] = 'EXT:xima_typo3_manual/Resources/Public/Css/Backend/Manual.css';
if ((new Typo3Version())->getMajorVersion() < 14) {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['xima_typo3_manual_v13'] = 'EXT:xima_typo3_manual/Resources/Public/Css/Backend/Manual-v13.css';
}

ExtensionUtility::configurePlugin(
    'IndexedSearch',
    'ManualResults',
    [SearchController::class => ['search']],
    [SearchController::class => ['search']],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

// EXT:visual_editor is optional. Its f:mark.contentArea ViewHelper only exists while the extension is installed, so
// the partial using it is layered on top of the default one instead of replacing it.
if (ExtensionManagementUtility::isLoaded('visual_editor')) {
    ExtensionManagementUtility::addTypoScriptSetup(
        'page.10.partialRootPaths.20 = EXT:xima_typo3_manual/Resources/Private/VisualEditor/Partials/'
    );
}

// TYPO3 v14 takes the allowed tables from the TCA option "allowedRecordTypes", see Configuration/TCA/Overrides/pages.php
if ((new Typo3Version())->getMajorVersion() < 14) {
    GeneralUtility::makeInstance(PageDoktypeRegistry::class)->add(Configuration::DOKTYPE_MANUAL, [
        'type' => 'web',
        'allowedTables' => implode(',', Configuration::ALLOWED_RECORD_TYPES),
    ]);
}
