<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\EventListener;

use Doctrine\DBAL\Exception;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownDivider;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownHeader;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownItem;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDownButton;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Controller\ManualController;
use Xima\XimaTypo3Manual\Service\ManualRegistry;

/**
 * Adds the manual button to every backend doc header. Which button is shown depends on what the current view is
 * documented by: a dropdown of matching chapters, a preview button while editing the manual itself, or a plain link
 * to the whole manual.
 */
final readonly class ModifyButtonBarEventListener
{
    public function __construct(
        private IconFactory $iconFactory,
        private UriBuilder $uriBuilder,
        private PageRenderer $pageRenderer,
        private ConnectionPool $connectionPool,
        private ManualRegistry $manualRegistry
    ) {
    }

    #[AsEventListener(identifier: 'xima-typo3-manual/modify-button-bar')]
    public function __invoke(ModifyButtonBarEvent $event): void
    {
        $request = $this->getRequest($event);
        if (!$request instanceof ServerRequestInterface) {
            return;
        }

        $pageId = $this->resolvePageId($request);

        $path = $request->getUri()->getPath();
        $isRecordEdit = str_contains($path, 'record/edit');
        if (str_contains($path, 'help/manual') || ($pageId === 0 && !$isRecordEdit)) {
            return;
        }

        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/ManualModal.js');
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf');

        $manualPages = $this->groupByManual($this->getManualPages($request, $pageId, $isRecordEdit));

        if ($manualPages !== []) {
            $button = $this->getDropdownManualButton($event, $manualPages);
        } elseif (ManualController::hasManualRootPage($pageId)) {
            $button = $this->getPreviewManualButton($event, $pageId);
        } else {
            $button = $this->getSmallManualButton($event, $pageId);
        }

        $buttons = $event->getButtons();
        $buttons['right'] ??= [];
        $buttons['right'][] = [$button];
        $event->setButtons($buttons);
    }

    /**
     * ModifyButtonBarEvent only carries the request since TYPO3 v14.
     */
    private function getRequest(ModifyButtonBarEvent $event): ?ServerRequestInterface
    {
        if (method_exists($event, 'getRequest')) {
            return $event->getRequest();
        }

        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;

        return $request instanceof ServerRequestInterface ? $request : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getManualPages(ServerRequestInterface $request, int $pageId, bool $isRecordEdit): array
    {
        $manualPages = [];

        if ($isRecordEdit) {
            [$recordTable, $recordUid] = $this->resolveEditedRecord($request);
            if ($recordTable !== null && $recordUid > 0) {
                $recordType = $this->getTypeField($recordTable);
                array_push($manualPages, ...$this->getRelatedRecords('tt_content', $recordUid, $recordTable, $recordType));
                array_push($manualPages, ...$this->getRelatedRecords('pages', $recordUid, $recordTable, $recordType));
                if ($recordTable === 'tt_content') {
                    array_push($manualPages, ...$this->getRelatedPluginRecords('tt_content', $recordUid));
                    array_push($manualPages, ...$this->getRelatedPluginRecords('pages', $recordUid));
                }
            }
        }

        if ($pageId > 0) {
            array_push($manualPages, ...$this->getRelatedRecords('pages', $pageId, 'pages', 'doktype'));
            array_push($manualPages, ...$this->getRelatedRecords('tt_content', $pageId, 'pages', 'doktype'));
        }

        return $manualPages;
    }

    /**
     * Finds the manual chapters (table "pages") or manual elements (table "tt_content") whose relation field points at
     * the type of the given record, e.g. "tt_content:textmedia".
     *
     * @return list<array<string, mixed>>
     */
    private function getRelatedRecords(string $manualTable, int $recordUid, string $sourceTable, ?string $typeField): array
    {
        if (!$this->isKnownTable($sourceTable) || !$this->isKnownTable($manualTable)) {
            return [];
        }

        $type = $this->getRecordType($sourceTable, $recordUid, $typeField);
        if ($type === null) {
            return [];
        }

        return $this->findManualRecords($manualTable, $sourceTable . ':' . $type);
    }

    /**
     * Plugins are documented per plugin signature. TYPO3 v13 stores it in "list_type", v14 dropped that column and
     * registers every plugin as its own CType.
     *
     * @return list<array<string, mixed>>
     */
    private function getRelatedPluginRecords(string $manualTable, int $recordUid): array
    {
        if (!$this->hasColumn('tt_content', 'list_type')) {
            return [];
        }

        $qb = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $row = $qb->select('CType', 'list_type')
            ->from('tt_content')
            ->where($qb->expr()->eq('uid', $qb->createNamedParameter($recordUid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        if (!is_array($row) || ($row['CType'] ?? '') !== 'list' || ($row['list_type'] ?? '') === '') {
            return [];
        }

        return $this->findManualRecords($manualTable, 'tt_content:list:' . $row['list_type']);
    }

    private function getRecordType(string $table, int $uid, ?string $typeField): ?string
    {
        // Tables without a type field are documented under their single TCA type, mirroring SelectItemsProcFunc
        if ($typeField === null) {
            $firstType = array_key_first($GLOBALS['TCA'][$table]['types'] ?? []);
            return $firstType === null ? null : (string)$firstType;
        }

        if (!$this->hasColumn($table, $typeField)) {
            return null;
        }

        $qb = $this->connectionPool->getQueryBuilderForTable($table);
        $type = $qb->select($typeField)
            ->from($table)
            ->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchOne();

        return $type === false || $type === null ? null : (string)$type;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findManualRecords(string $manualTable, string $relation): array
    {
        $qb = $this->connectionPool->getQueryBuilderForTable($manualTable);
        $qb->getRestrictions()->removeAll();

        $fields = $manualTable === 'pages' ? ['uid', 'title'] : ['uid', 'pid', 'header'];

        try {
            /** @var list<array<string, mixed>> $rows */
            $rows = $qb->select(...$fields)
                ->from($manualTable)
                ->where(
                    $qb->expr()->eq('deleted', $qb->createNamedParameter(0, Connection::PARAM_INT)),
                    $qb->expr()->eq('hidden', $qb->createNamedParameter(0, Connection::PARAM_INT)),
                    sprintf(
                        'FIND_IN_SET(%s, %s)',
                        $qb->createNamedParameter($relation),
                        $qb->quoteIdentifier('tx_ximatypo3manual_relations')
                    )
                )
                ->executeQuery()
                ->fetchAllAssociative();
        } catch (Exception) {
            return [];
        }

        return $rows;
    }

    /**
     * @return array{0: string|null, 1: int}
     */
    private function resolveEditedRecord(ServerRequestInterface $request): array
    {
        $editParam = $request->getQueryParams()['edit'] ?? null;
        if (!is_array($editParam) || $editParam === []) {
            return [null, 0];
        }

        $table = (string)array_key_first($editParam);
        $records = $editParam[$table] ?? [];
        if (!is_array($records) || $records === []) {
            return [null, 0];
        }

        return [$table, (int)array_key_first($records)];
    }

    private function getTypeField(string $table): ?string
    {
        $type = $GLOBALS['TCA'][$table]['ctrl']['type'] ?? '';
        if (!is_string($type) || $type === '') {
            return null;
        }

        // A type field may point into a relation ("uid_local:type"), only the local column is usable here
        return explode(':', $type)[0];
    }

    private function isKnownTable(string $table): bool
    {
        return isset($GLOBALS['TCA'][$table]);
    }

    private function hasColumn(string $table, string $column): bool
    {
        return isset($GLOBALS['TCA'][$table]['columns'][$column]);
    }

    private function resolvePageId(ServerRequestInterface $request): int
    {
        $parsedBody = $request->getParsedBody();
        $id = (is_array($parsedBody) ? $parsedBody['id'] ?? null : null)
            ?? $request->getQueryParams()['id']
            ?? 0;

        return max(0, (int)$id);
    }

    /**
     * @param array<int, list<array<string, mixed>>> $manualPages Chapters grouped by the manual they belong to
     */
    public function getDropdownManualButton(ModifyButtonBarEvent $event, array $manualPages): DropDownButton
    {
        $dropdown = GeneralUtility::makeInstance(DropDownButton::class);
        $dropdown->setLabel($this->translate('button.dropdown'));
        $dropdown->setTitle($this->translate('button.dropdown.title'));
        $dropdown->setShowLabelText(true);
        $dropdown->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', IconSize::SMALL));

        $showManualTitles = count($manualPages) > 1;

        if (!$showManualTitles) {
            $dropdown->addItem(
                GeneralUtility::makeInstance(DropDownHeader::class)
                    ->setLabel($this->translate('button.dropdown.header'))
            );
        }

        $key = 0;
        foreach ($manualPages as $manualRoot => $chapters) {
            if ($showManualTitles) {
                $manualTitle = $this->manualRegistry->getTitle($manualRoot);
                $dropdown->addItem(
                    GeneralUtility::makeInstance(DropDownHeader::class)
                        ->setLabel($manualTitle !== '' ? $manualTitle : $this->translate('button.dropdown.header'))
                );
            }

            foreach ($chapters as $manualPage) {
                $key++;
                $item = GeneralUtility::makeInstance(DropDownItem::class);
                $title = (string)($manualPage['title'] ?? $manualPage['header'] ?? '');
                $title = $title !== '' ? $title : $this->translate('button.dropdown.no-title') . ' ' . $key;
                $pid = (int)($manualPage['pid'] ?? $manualPage['uid'] ?? 0);

                $item->setIcon($this->iconFactory->getIcon('actions-dot', IconSize::SMALL));
                $item->setLabel($title);
                $item->setAttributes([
                    'data-manual-modal' => 'open',
                    'data-manual-backend-url' => $this->manualUri($pid, 'backend'),
                ]);
                $item->setHref($this->manualUri($pid, 'iframe'));
                $dropdown->addItem($item);
            }
        }

        $dropdown->addItem(GeneralUtility::makeInstance(DropDownDivider::class));

        $allChapters = GeneralUtility::makeInstance(DropDownItem::class);
        $allChapters->setHref($this->manualUri(0, 'iframe'));
        $allChapters->setTitle($this->translate('button.dropdown.all.title'));
        $allChapters->setLabel($this->translate('button.dropdown.all'));
        $allChapters->setAttributes([
            'data-manual-modal' => 'open',
            'data-manual-backend-url' => $this->manualUri(0, 'backend'),
        ]);
        $allChapters->setIcon($this->iconFactory->getIcon('actions-notebook', IconSize::SMALL));
        $dropdown->addItem($allChapters);

        return $dropdown;
    }

    private function getPreviewManualButton(ModifyButtonBarEvent $event, int $pageId): LinkButton
    {
        $button = GeneralUtility::makeInstance(LinkButton::class);

        return $button
            ->setHref($this->manualUri($pageId, 'backend'))
            ->setTitle($this->translate('button.preview'))
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', IconSize::SMALL))
            ->setDataAttributes(['manual-preview' => ManualController::getRootPageUid($pageId)]);
    }

    public function getSmallManualButton(ModifyButtonBarEvent $event, int $pageId): LinkButton
    {
        $button = GeneralUtility::makeInstance(LinkButton::class);

        return $button
            ->setHref($this->manualUri($pageId, 'iframe'))
            ->setTitle($this->translate('button.dropdown.all.title'))
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', IconSize::SMALL))
            ->setDataAttributes([
                'manual-modal' => 'open',
                'manual-backend-url' => $this->manualUri($pageId, 'backend'),
            ]);
    }

    /**
     * Chapters of manuals the user may not read are dropped, the rest is grouped by the manual they belong to.
     *
     * @param list<array<string, mixed>> $manualPages
     * @return array<int, list<array<string, mixed>>>
     */
    private function groupByManual(array $manualPages): array
    {
        $grouped = [];
        foreach ($manualPages as $manualPage) {
            $pageUid = (int)($manualPage['pid'] ?? $manualPage['uid'] ?? 0);
            $manualRoot = $this->manualRegistry->getManualRootForPage($pageUid);
            if ($manualRoot === 0 || !$this->manualRegistry->isAccessible($manualRoot)) {
                continue;
            }
            $grouped[$manualRoot][] = $manualPage;
        }

        return $grouped;
    }

    private function manualUri(int $pageId, string $context): string
    {
        return (string)$this->uriBuilder->buildUriFromRoute(
            Configuration::EXTENSION_KEY,
            [
                'id' => $pageId,
                'context' => $context,
                'language' => $this->getBackendUser()->uc['lang'] ?? '',
            ]
        );
    }

    private function translate(string $key): string
    {
        return $this->getLanguageService()?->sL(Configuration::LANGUAGE_FILE . $key) ?? $key;
    }

    private function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
