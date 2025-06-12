<?php

namespace Xima\XimaTypo3Manual\EventListener;

use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class PageContentPreviewEventListener
{
    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content') {
            return;
        }

        $record = $event->getRecord();

        if ($record['CType'] === 'msteps') {
            $event->setPreviewContent($this->getMstepsPreviewHtml($event->getRecord()));
        }

        if ($record['CType'] === 'mbox') {
            $event->setPreviewContent($this->getMboxPreviewHtml($event->getRecord()));
        }
    }

    private function getMstepsPreviewHtml(array $record): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:xima_typo3_manual/Resources/Private/Backend/MstepsPreview.html');

        if ($record['tx_ximatypo3manual_children'] !== 0) {
            $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $steps = $qb->select('header')
                ->from('tt_content')
                ->where(
                    $qb->expr()->eq('tx_ximatypo3manual_parent', $record['uid']),
                )
                ->orderBy('sorting', 'ASC')
                ->executeQuery()
                ->fetchAllAssociative();

            $view->assign('steps', $steps);
        }

        $view->assign('data', $record);

        return $view->render();
    }

    private function getMboxPreviewHtml(array $record): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:xima_typo3_manual/Resources/Private/Backend/MboxPreview.html');

        $stateMapping = [
            0 => -2,
            1 => -1,
            2 => 0,
            3 => 1,
            4 => 2,
        ];
        $view->assign('state', $stateMapping[$record['layout']] ?? -2);
        $view->assign('data', $record);

        return $view->render();
    }
}
