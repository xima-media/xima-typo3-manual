<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\EventListener;

use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

final readonly class PageContentPreviewEventListener
{
    /**
     * Infobox states of EXT:backend, indexed by the "layout" value of an mbox element.
     */
    private const MBOX_STATES = [0 => -2, 1 => -1, 2 => 0, 3 => 1, 4 => 2];

    public function __construct(
        private ConnectionPool $connectionPool,
        private ViewFactoryInterface $viewFactory
    ) {
    }

    #[AsEventListener(identifier: 'xima-typo3-manual/page-content-preview')]
    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content') {
            return;
        }

        $record = $this->toArray($event->getRecord());

        $preview = match ($record['CType'] ?? '') {
            'msteps' => $this->renderPreview('Backend/MstepsPreview', [
                'data' => $record,
                'steps' => $this->getSteps((int)($record['uid'] ?? 0)),
            ]),
            'mbox' => $this->renderPreview('Backend/MboxPreview', [
                'data' => $record,
                'state' => self::MBOX_STATES[(int)($record['layout'] ?? 0)] ?? -2,
            ]),
            default => null,
        };

        if ($preview !== null) {
            $event->setPreviewContent($preview);
        }
    }

    /**
     * TYPO3 v13 hands over a plain record array, v14 a RecordInterface.
     *
     * @param RecordInterface|array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function toArray(RecordInterface|array $record): array
    {
        return $record instanceof RecordInterface ? $record->toArray() : $record;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getSteps(int $parentUid): array
    {
        if ($parentUid === 0) {
            return [];
        }

        $qb = $this->connectionPool->getQueryBuilderForTable('tt_content');

        return $qb->select('uid', 'header')
            ->from('tt_content')
            ->where(
                $qb->expr()->eq(
                    'tx_ximatypo3manual_parent',
                    $qb->createNamedParameter($parentUid, Connection::PARAM_INT)
                )
            )
            ->orderBy('sorting', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function renderPreview(string $template, array $variables): string
    {
        $view = $this->viewFactory->create(new ViewFactoryData(
            templateRootPaths: ['EXT:xima_typo3_manual/Resources/Private/'],
        ));
        $view->assignMultiple($variables);

        return $view->render($template);
    }
}
