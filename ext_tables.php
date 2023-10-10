<?php

(function () {
    $GLOBALS['PAGES_TYPES'][701] = [
        'type' => 'web',
        'allowedTables' => 'pages,tt_content,sys_template,sys_file_reference',
    ];

    $GLOBALS['TBE_STYLES']['skins']['xima_typo3_manual']['stylesheetDirectories'][] = 'EXT:xima_typo3_manual/Resources/Public/Css/Backend/';
})();
