<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addStaticFile(
    'xima_typo3_manual',
    'Configuration/TypoScript',
    'XIMA Manual'
);
