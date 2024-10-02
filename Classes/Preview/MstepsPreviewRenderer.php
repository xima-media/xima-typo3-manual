<?php

namespace Xima\XimaTypo3Manual\Preview;

use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Preview\TextmediaPreviewRenderer;

class MstepsPreviewRenderer extends TextmediaPreviewRenderer
{
    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $row = $item->getRecord();

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:xima_typo3_manual/Resources/Private/Backend/MstepsPreview.html');

        if ($row['tx_ximatypo3manual_children'] !== 0) {
            $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $steps = $qb->select('header')
                ->from('tt_content')
                ->where(
                    $qb->expr()->eq('tx_ximatypo3manual_parent', $row['uid'])
                )
                ->orderBy('sorting', 'ASC')
                ->executeQuery()
                ->fetchAllAssociative();

            $view->assign('steps', $steps);
        }

        $view->assign('data', $row);

        return $view->render();
    }
}
