<?php

/**
 * Definitions for modules provided by EXT:examples
 */

use Xima\XimaTypo3Manual\Controller\ManualController;

return [
    'xima_typo3_manual' => [
        'parent' => 'help',
        'position' => ['before' => '*'],
        'workspaces' => '*',
        'access' => 'user,group',
        'path' => '/module/help/manual',
        'aliases' => ['help_manual'],
        'icon' => 'EXT:xima_typo3_manual/Resources/Public/Icons/icon-module.svg',
        'labels' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf',
        'navigationComponent' => '@typo3/backend/page-tree/page-tree-element',
        'extensionName' => 'xima_typo3_manual',
        'controllerActions' => [
            ManualController::class => 'index',
        ],
    ],
];
