<?php

namespace Xima\XimaTypo3Manual\EventListener;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownItem;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ModifyButtonBarEventListener
{
    public function __construct(private IconFactory $iconFactory, private UriBuilder $uriBuilder)
    {
    }

    public function __invoke(ModifyButtonBarEvent $event): void
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $pageId = (int)($request->getParsedBody()['id'] ?? $request->getQueryParams()['id'] ?? 0);

        $uri = $request->getUri()->getPath();
        if (str_contains($uri, 'help/manual') || (!$pageId && !str_contains($uri, 'record/edit'))) {
            return;
        }

        $manualPages = [];

        // edit record view
        if (str_contains($uri, 'record/edit')) {
            $editParam = $request->getQueryParams()['edit'] ?? '';
            $recordTable = array_key_first($editParam);
            $recordUid = (int)array_key_first($editParam[$recordTable] ?? []);
            $recordType = $GLOBALS['TCA'][$recordTable]['ctrl']['type'] ?? '0';
            array_push($manualPages, ...$this->getManualElementsForRecord($recordUid, $recordTable, $recordType));
            array_push($manualPages, ...$this->getManualPagesForRecord($recordUid, $recordTable, $recordType));
        }

        // page view, list view, etc.
        if ($pageId) {
            array_push($manualPages, ...$this->getManualPagesForRecord($pageId, 'pages', 'doktype'));
        }

        $buttons = $event->getButtons();
        $buttons['right'] ??= [];

        if (count($manualPages)) {
            $dropdown = $event->getButtonBar()->makeDropDownButton();
            $dropdown->setLabel($GLOBALS['LANG']->sL('LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:button.dropdown'));
            $dropdown->setTitle('Open manual page');
            $dropdown->setShowLabelText(true);
            $dropdown->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', Icon::SIZE_SMALL));
            foreach ($manualPages as $manualPage) {
                // manual page
                if (isset($manualPage['title'])) {
                    $dropdown->addItem(
                        GeneralUtility::makeInstance(DropDownItem::class)
                            ->setLabel($manualPage['title'])
                            ->setHref($this->uriBuilder->buildUriFromRoute('xima_typo3_manual',
                                ['id' => $manualPage['uid']]))
                    );
                }
                // manual element
                if (isset($manualPage['header'])) {
                    $dropdown->addItem(
                        GeneralUtility::makeInstance(DropDownItem::class)
                            ->setLabel($manualPage['header'])
                            ->setHref($this->uriBuilder->buildUriFromRoute('xima_typo3_manual',
                                ['id' => $manualPage['pid']]))
                    );
                }
            }
            $buttons['right'][] = [$dropdown];
        } else {
            $manualButton = $event->getButtonBar()->makeLinkButton();
            $manualButton->setHref($this->uriBuilder->buildUriFromRoute('xima_typo3_manual', ['id' => $pageId]));
            $manualButton->setTitle($GLOBALS['LANG']->sL('LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:button.dropdown'));
            $manualButton->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', Icon::SIZE_SMALL));

            $buttons['right'][] = [$manualButton];
        }

        $event->setButtons($buttons);
    }

    protected function getManualElementsForRecord(int $recordUid, string $recordTable, string $recordType): array
    {
        $sql = sprintf('select c.uid, c.pid, c.header from %s r, tt_content c where r.uid=%s and FIND_IN_SET(concat("%s:", r.%s), (c.tx_ximatypo3manual_relations)) and c.deleted=0 and c.hidden=0',
            $recordTable, $recordUid, $recordTable, $recordType);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');
        $result = $connection->executeQuery($sql)->fetchAllAssociative();

        if (!$result) {
            return [];
        }

        return $result;
    }

    protected function getManualPagesForRecord(int $recordUid, string $recordTable, string $recordType): array
    {
        $sql = sprintf('select p.uid, p.title from %s r, pages p where r.uid=%s and FIND_IN_SET(concat("%s:", r.%s), (p.tx_ximatypo3manual_relations)) and p.deleted=0 and p.hidden=0',
            $recordTable, $recordUid, $recordTable, $recordType);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->executeQuery($sql)->fetchAllAssociative();

        if (!$result) {
            return [];
        }

        return $result;
    }
}
