<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Service;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Domain\RecordType;

/**
 * Answers "what does this manual actually document?" by matching every documentable record type against the
 * relations stored on the manual's chapters and elements.
 */
class CoverageService
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly RecordTypeRegistry $recordTypeRegistry
    ) {
    }

    /**
     * @return array{rows: list<array{recordType: RecordType, documentedBy: list<array{uid: int, pid: int, title: string, table: string}>}>, documented: int, total: int}
     */
    public function getCoverage(int $manualRootPageUid): array
    {
        $manualPageUids = $this->getManualPageUids($manualRootPageUid);
        $relations = $manualPageUids === [] ? [] : $this->getRelations($manualPageUids);

        $rows = [];
        $documented = 0;
        foreach ($this->recordTypeRegistry->getAll() as $recordType) {
            $documentedBy = $relations[$recordType->identifier] ?? [];
            if ($documentedBy !== []) {
                $documented++;
            }
            $rows[] = [
                'recordType' => $recordType,
                'documentedBy' => $documentedBy,
            ];
        }

        return [
            'rows' => $rows,
            'documented' => $documented,
            'total' => count($rows),
        ];
    }

    /**
     * @param list<int> $manualPageUids
     * @return array<string, list<array{uid: int, pid: int, title: string, table: string}>>
     */
    private function getRelations(array $manualPageUids): array
    {
        $relations = [];

        foreach ([['pages', 'title', 'uid'], ['tt_content', 'header', 'pid']] as [$table, $titleField, $pidField]) {
            $qb = $this->connectionPool->getQueryBuilderForTable($table);
            $rows = $qb->select('uid', $pidField, $titleField, 'tx_ximatypo3manual_relations')
                ->from($table)
                ->where(
                    $qb->expr()->in(
                        $table === 'pages' ? 'uid' : 'pid',
                        $qb->createNamedParameter($manualPageUids, Connection::PARAM_INT_ARRAY)
                    ),
                    $qb->expr()->neq('tx_ximatypo3manual_relations', $qb->createNamedParameter(''))
                )
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($rows as $row) {
                $entry = [
                    'uid' => (int)$row['uid'],
                    'pid' => (int)$row[$pidField],
                    'title' => (string)($row[$titleField] ?? ''),
                    'table' => $table,
                ];
                foreach (GeneralUtility::trimExplode(',', (string)$row['tx_ximatypo3manual_relations'], true) as $identifier) {
                    $relations[$identifier][] = $entry;
                }
            }
        }

        return $relations;
    }

    /**
     * @return list<int>
     */
    private function getManualPageUids(int $manualRootPageUid): array
    {
        if ($manualRootPageUid <= 0) {
            return [];
        }

        $uids = [$manualRootPageUid];
        $queue = [$manualRootPageUid];

        // The page tree of a manual is small, so collecting it level by level keeps the query count predictable
        while ($queue !== []) {
            $qb = $this->connectionPool->getQueryBuilderForTable('pages');
            $children = $qb->select('uid')
                ->from('pages')
                ->where(
                    $qb->expr()->in('pid', $qb->createNamedParameter($queue, Connection::PARAM_INT_ARRAY)),
                    $qb->expr()->eq('doktype', $qb->createNamedParameter(Configuration::DOKTYPE_MANUAL, Connection::PARAM_INT))
                )
                ->executeQuery()
                ->fetchFirstColumn();

            $queue = array_values(array_diff(array_map(intval(...), $children), $uids));
            $uids = [...$uids, ...$queue];
        }

        return $uids;
    }
}
