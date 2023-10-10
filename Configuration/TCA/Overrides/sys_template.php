<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Xima\XimaTypo3Manual\Configuration;

ExtensionManagementUtility::addStaticFile(
    Configuration::EXT_KEY,
    'Configuration/TypoScript',
    'Xima Manual'
);
