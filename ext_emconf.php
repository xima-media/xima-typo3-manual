<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Editor manual for TYPO3 backend',
    'description' => 'This extension provides a new page type for creating an editor manual right in the TYPO3 backend.',
    'category' => 'module',
    'author' => 'Maik Schneider',
    'author_email' => 'maik.schneider@xima.de',
    'author_company' => 'XIMA Media GmbH',
    'state' => 'stable',
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'php' => '8.2.0-8.5.99',
            'typo3' => '13.4.0-14.3.99',
            'filelist' => '13.4.0-14.3.99',
            'bw_focuspoint_images' => '6.0.0-7.99.99',
            'bw_icons' => '4.0.0-4.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'visual_editor' => '1.10.0-1.99.99',
        ],
    ],
];
