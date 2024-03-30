<?php

namespace Xima\XimaTypo3Manual\Generator;

use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class ManualGenerator
{
    public function createManualFromPreset(string $presetIdentifier): array
    {
        $data = $this->getDataForPreset($presetIdentifier);
        if (empty($data)) {
            return [];
        }

        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->enableLogging = false;
        $dataHandler->bypassAccessCheckForRecords = true;
        $dataHandler->bypassWorkspaceRestrictions = true;
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        $rootPageUid = $dataHandler->substNEWwithIDs['NEW1'];
        $this->createSiteConfiguration($rootPageUid);

        return [
            'rootPageUid' => $rootPageUid,
        ];
    }

    protected function getDataForPreset(string $presetIdentifier): array
    {
        if ($presetIdentifier === '1') {
            return self::getEmptyManualPresetData();
        }
        return [];
    }

    protected static function getEmptyManualPresetData(): array
    {
        return [
            'pages' => [
                'NEW1' => [
                    'pid' => 0 - self::getUidOfLastTopLevelPage(),
                    'hidden' => 0,
                    'title' => 'New Manual',
                    'doktype' => 701,
                    'is_siteroot' => 1,
                    'tsconfig_includes' => 'EXT:xima_typo3_manual/Configuration/TSconfig/Page.tsconfig',
                    'backend_layout' => 'pagets__manualHomepage',
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

    protected function createSiteConfiguration(
        int $rootPageUid,
        string $title = 'manual demo'
    ): void {
        $port = $GLOBALS['TYPO3_REQUEST']->getUri()->getPort() ? ':' . $GLOBALS['TYPO3_REQUEST']->getUri()->getPort() : '';
        $domain = $GLOBALS['TYPO3_REQUEST']->getUri()->getScheme() . '://' . $GLOBALS['TYPO3_REQUEST']->getUri()->getHost() . $port . '/';

        $siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
        $siteIdentifier = 'manual-demo-' . $rootPageUid;
        $configuration = [
            'base' => $domain . 'manual-demo-' . $rootPageUid,
            'rootPageId' => $rootPageUid,
            'routes' => [],
            'websiteTitle' => $title . ' ' . $rootPageUid,
            'baseVariants' => [],
            'errorHandling' => [],
            'languages' => [
                [
                    'title' => 'English',
                    'enabled' => true,
                    'languageId' => 0,
                    'base' => '/',
                    'typo3Language' => 'default',
                    'locale' => 'en_US.UTF-8',
                    'iso-639-1' => 'en',
                    'navigationTitle' => 'English',
                    'hreflang' => 'en-us',
                    'direction' => 'ltr',
                    'flag' => 'us',
                    'websiteTitle' => '',
                ],
            ],
        ];
        $siteConfiguration->write($siteIdentifier, $configuration);
    }
}
