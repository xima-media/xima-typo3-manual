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
    'version' => '2.0.5',
    'constraints' => [
        'depends' => [
            'php' => '8.2.0-8.4.99',
            'typo3' => '12.0.0-13.4.99',
            'bw_focuspoint_images' => '4.0.0-4.99.99',
            'bw_icons' => '3.0.0-3.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
