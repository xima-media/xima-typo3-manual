<?php

use TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(static function (): void {
    GeneralUtility::makeInstance(PageDoktypeRegistry::class)->add(701, [
        'type' => 'web',
        'allowedTables' => 'pages,tt_content,sys_template,sys_file_reference',
    ]);
})();
