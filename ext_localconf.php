<?php

declare(strict_types=1);

use TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XimaTypo3Manual\Configuration;

defined('TYPO3') || die();

$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['xima_typo3_manual'] = 'EXT:xima_typo3_manual/Configuration/RTE/Manual.yaml';

// TYPO3 v14 takes the allowed tables from the TCA option "allowedRecordTypes", see Configuration/TCA/Overrides/pages.php
if ((new Typo3Version())->getMajorVersion() < 14) {
    GeneralUtility::makeInstance(PageDoktypeRegistry::class)->add(Configuration::DOKTYPE_MANUAL, [
        'type' => 'web',
        'allowedTables' => implode(',', Configuration::ALLOWED_RECORD_TYPES),
    ]);
}
