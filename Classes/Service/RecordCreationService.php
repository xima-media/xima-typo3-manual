<?php

namespace Xima\XimaTypo3Manual\Service;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class RecordCreationService
{
    public function createNewManual(): array
    {
        $data = self::getNewRootPageData();

        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        $rootPageUid = $dataHandler->substNEWwithIDs['NEW1'];

        return [
            'rootPageUid' => $rootPageUid,
        ];
    }

    protected static function getNewRootPageData(): array
    {
        return [
            'pages' => [
                'NEW1' => [
                    'pid' => 0 - self::getUidOfLastTopLevelPage(),
                    'hidden' => 0,
                    'title' => 'New Manual',
                    'doktype' => 701,
                    'is_siteroot' => 1,
                    'slug' => '/',
                    'tsconfig_includes' => 'EXT:xima_typo3_manual/Configuration/TSconfig/Page.tsconfig',
                ],
            ],
            'sys_template' => [
                'NEW2' => [
                    'pid' => 'NEW1',
                    'title' => 'NEW MANUAL',
                    'root' => 1,
                    'clear' => 3,
                    'include_static_file' => 'EXT:xima_typo3_manual/Configuration/TypoScript',
                ],
            ],
        ];
    }

    protected static function getUidOfLastTopLevelPage(): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $lastPage = $queryBuilder->select('uid')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)))
            ->orderBy('sorting', 'DESC')
            ->executeQuery()
            ->fetchOne();
        $uid = 0;
        if (MathUtility::canBeInterpretedAsInteger($lastPage) && $lastPage > 0) {
            $uid = (int)$lastPage;
        }
        return $uid;
    }
}
