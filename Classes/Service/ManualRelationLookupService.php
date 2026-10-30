<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Service;

use Doctrine\DBAL\Exception;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Xima\XimaTypo3Manual\Configuration;

/**
 * Finds the manual chapters (table "pages") and manual elements (table "tt_content") that document a given record or
 * page, by matching their relation field against the record's type, e.g. "tt_content:textmedia".
 */
final readonly class ManualRelationLookupService
{
    public function __construct(
        private ConnectionPool $connectionPool
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findForRecord(ServerRequestInterface $request, int $pageId, bool $isRecordEdit): array
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
                        $qb->quoteIdentifier(Configuration::RELATIONS_FIELD)
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
}
