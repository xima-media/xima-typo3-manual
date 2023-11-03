<?php

use TYPO3\CMS\Backend\Controller\Page\TreeController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

//$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TreeController::class] = [
//    'className' => Xima\XimaTypo3Manual\Controller\TreeController::class,
//];

ExtensionManagementUtility::addUserTSConfig(
    'options.pageTree.doktypesToShowInNewPageDragArea := addToList(701)'
);

//$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
//$pageRenderer->loadRequireJsModule('TYPO3/CMS/XimaTypo3Manual/ManualGlobal');

$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['xima_typo3_manual'] = 'EXT:xima_typo3_manual/Configuration/RTE/Manual.yaml';
