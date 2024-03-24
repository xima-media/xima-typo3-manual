<?php

namespace Xima\XimaTypo3Manual\Preview;

use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Preview\TextmediaPreviewRenderer;

class MboxPreviewRenderer extends TextmediaPreviewRenderer
{
    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $row = $item->getRecord();

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:xima_typo3_manual/Resources/Private/Backend/MboxPreview.html');

        $stateMapping = [
            0 => -2,
            1 => -1,
            2 => 0,
            3 => 1,
            4 => 2,
        ];
        $view->assign('state', $stateMapping[$row['layout']] ?? -2);
        $view->assign('data', $row);

        return $view->render();
    }
}
