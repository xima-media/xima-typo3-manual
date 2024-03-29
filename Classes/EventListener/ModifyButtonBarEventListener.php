<?php

namespace Xima\XimaTypo3Manual\EventListener;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownDivider;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownHeader;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownItem;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownItemInterface;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDownButton;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ModifyButtonBarEventListener
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

        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/ManualModal.js');
        $pageRenderer->addInlineLanguageLabelFile('EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf');

        // extract manual pages that fit current view
        $manualPages = $this->getManualPages($uri, $request, $pageId);

        $buttons = $event->getButtons();
        $buttons['right'] ??= [];

        if (count($manualPages)) {
            $button = $this->getDropdownManualButton($event, $manualPages);
        } else {
            $button = $this->getSmallManualButton($event, $pageId);
        }

        $buttons['right'][] = [$button];
        $event->setButtons($buttons);
    }

    public function getManualPages(string $uri, ServerRequestInterface $request, int $pageId): array
    {
        $manualPages = [];

        // edit record view
        if (str_contains($uri, 'record/edit')) {
            $editParam = $request->getQueryParams()['edit'] ?? '';
            $recordTable = array_key_first($editParam);
            $recordUid = (int)array_key_first($editParam[$recordTable] ?? []);
            $recordType = $GLOBALS['TCA'][$recordTable]['ctrl']['type'] ?? '0';
            array_push($manualPages, ...$this->getManualElementsForRecord($recordUid, $recordTable, $recordType));
            array_push($manualPages, ...$this->getManualPagesForRecord($recordUid, $recordTable, $recordType));
            if ($recordTable === 'tt_content') {
                array_push($manualPages, ...$this->getManualElementsForPlugin($recordUid));
                array_push($manualPages, ...$this->getManualPagesForPlugin($recordUid));
            }
        }

        // page view, list view, etc.
        if ($pageId) {
            array_push($manualPages, ...$this->getManualPagesForRecord($pageId, 'pages', 'doktype'));
        }

        return $manualPages;
    }

    protected function getManualElementsForRecord(int $recordUid, string $recordTable, string $recordType): array
    {
        return $this->fetchRecords(
            sprintf(
                'select c.uid, c.pid, c.header from %s r, tt_content c where r.uid=%s and FIND_IN_SET(concat("%s:", r.%s), (c.tx_ximatypo3manual_relations)) and c.deleted=0 and c.hidden=0',
                $recordTable,
                $recordUid,
                $recordTable,
                $recordType
            ),
            'tt_content'
        );
    }

    protected function getManualPagesForRecord(int $recordUid, string $recordTable, string $recordType): array
    {
        return $this->fetchRecords(
            sprintf(
                'select p.uid, p.title from %s r, pages p where r.uid=%s and FIND_IN_SET(concat("%s:", r.%s), (p.tx_ximatypo3manual_relations)) and p.deleted=0 and p.hidden=0',
                $recordTable,
                $recordUid,
                $recordTable,
                $recordType
            ),
            'pages'
        );
    }

    protected function getManualElementsForPlugin(int $recordUid): array
    {
        return $this->fetchRecords(
            sprintf(
                'select c.uid, c.pid, c.header from tt_content r, tt_content c where r.uid=%s and FIND_IN_SET(concat("tt_content:list:", r.list_type), (c.tx_ximatypo3manual_relations)) and c.deleted=0 and c.hidden=0 and r.CType="list" and r.list_type!=""',
                $recordUid
            ),
            'tt_content'
        );
    }

    protected function getManualPagesForPlugin(int $recordUid): array
    {
        return $this->fetchRecords(
            sprintf(
                'select p.uid, p.title from tt_content r, pages p where r.uid=%s and FIND_IN_SET(concat("tt_content:list:", r.list_type), (p.tx_ximatypo3manual_relations)) and p.deleted=0 and p.hidden=0 and r.CType="list" and r.list_type!=""',
                $recordUid
            ),
            'pages'
        );
    }

    protected function fetchRecords(string $sql, string $table): array
    {
        try {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
            $result = $connection->executeQuery($sql)->fetchAllAssociative();
        } catch (Exception) {
            return [];
        }

        return $result;
    }

    public function getDropdownManualButton(
        ModifyButtonBarEvent $event,
        array $manualPages
    ): DropDownButton {
        // create dropdown button
        $dropdown = $event->getButtonBar()->makeDropDownButton();
        $dropdown->setLabel($GLOBALS['LANG']->sL('LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:button.dropdown'));
        $dropdown->setTitle($GLOBALS['LANG']->sL('LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:button.dropdown.title'));
        $dropdown->setShowLabelText(true);
        $dropdown->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', Icon::SIZE_SMALL));

        // headline
        $dropdown->addItem(
            GeneralUtility::makeInstance(DropDownHeader::class)
                ->setLabel($GLOBALS['LANG']->sL('LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:button.dropdown.header'))
        );

        // chapter items
        foreach ($manualPages as $manualPage) {
            $title = $manualPage['title'] ?? $manualPage['header'];
            $pid = $manualPage['pid'] ?? $manualPage['uid'];
            /** @var DropDownItemInterface $dropdownItem */
            $dropdownItem = GeneralUtility::makeInstance(DropDownItem::class)
                ->setIcon($this->iconFactory->getIcon('actions-dot', Icon::SIZE_SMALL))
                ->setLabel($title)
                ->setAttributes(['data-manual-modal' => 'open'])
                ->setHref($this->uriBuilder->buildUriFromRoute(
                    'xima_typo3_manual',
                    ['id' => $pid, 'context' => 'iframe']
                ));
            $dropdown->addItem($dropdownItem);
        }

        // divider
        $dropdown->addItem(GeneralUtility::makeInstance(DropDownDivider::class));

        // generic manual link
        /** @var DropDownItemInterface $dropdownItem */
        $dropdownItem = GeneralUtility::makeInstance(DropDownItem::class)
            ->setHref($this->uriBuilder->buildUriFromRoute('xima_typo3_manual', ['id' => 0, 'context' => 'iframe']))
            ->setTitle($GLOBALS['LANG']->sL('LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:button.dropdown.all.title'))
            ->setLabel($GLOBALS['LANG']->sL('LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:button.dropdown.all'))
            ->setAttributes(['data-manual-modal' => 'open'])
            ->setIcon($this->iconFactory->getIcon('actions-notebook', Icon::SIZE_SMALL));
        $dropdown->addItem($dropdownItem);

        return $dropdown;
    }

    public function getSmallManualButton(
        ModifyButtonBarEvent $event,
        int $pageId
    ): \TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton {
        $manualButton = $event->getButtonBar()->makeLinkButton();
        $manualButton->setHref($this->uriBuilder->buildUriFromRoute('xima_typo3_manual', ['id' => $pageId, 'context' => 'iframe']));
        $manualButton->setTitle($GLOBALS['LANG']->sL('LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:button.dropdown.all.title'));
        $manualButton->setShowLabelText(true);
        $manualButton->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', Icon::SIZE_SMALL));
        $manualButton->setDataAttributes(['manual-modal' => 'open']);
        return $manualButton;
    }
}
