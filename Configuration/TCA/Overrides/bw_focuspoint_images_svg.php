<?php

$showItem = $GLOBALS['TCA']['tt_content']['types']['bw_focuspoint_images_svg']['showitem'];
$GLOBALS['TCA']['tt_content']['types']['bw_focuspoint_images_svg']['showitem'] = str_replace('assets,', 'bodytext,assets,', (string)$showItem);

$GLOBALS['TCA']['tt_content']['types']['bw_focuspoint_images_svg']['columnsOverrides']['bodytext'] = [
    'config' => [
        'enableRichtext' => true,
        'richtextConfiguration' => 'xima_typo3_manual',
    ],
];
