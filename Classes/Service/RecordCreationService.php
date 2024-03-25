<?php

namespace Xima\XimaTypo3Manual\Service;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $sorting = self::getSortingForNewRootPage();

        return [
            'pages' => [
                'NEW1' => [
                    'pid' => 0,
                    'hidden' => 0,
                    'title' => 'New Manual',
                    'doktype' => 701,
                    'is_siteroot' => 1,
                    'slug' => '/',
                    'tsconfig_includes' => 'EXT:xima_typo3_manual/Configuration/TSconfig/Page.tsconfig',
                    'sorting' => $sorting,
                ],
            ],
            'sys_template' => [
                'NEW2' => [
                    'pid' => 'NEW1',
                    'title' => 'NEW MANUAL',
                    'root' => 1,
                    'clear' => 3,
                    'include_static_file' => 'EXT:xima_typo3_manual/Configuration/TypoScript',
                    'sorting' => $sorting,
                ],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    protected static function getSortingForNewRootPage()
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $qb->getRestrictions()->removeAll();

        return $qb->addSelectLiteral('MAX(sorting)+1 as sorting')
            ->from('pages')
            ->where($qb->expr()->eq('pid', 0))
            ->executeQuery()
            ->fetchOne();
    }
}
