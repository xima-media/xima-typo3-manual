<?php

namespace Xima\XimaTypo3Manual\Generator;

use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use Xima\XimaTypo3Manual\Generator\Preset\EmptyManualPreset;
use Xima\XimaTypo3Manual\Generator\Preset\PresetInterface;

class ManualGenerator
{
    protected ?PresetInterface $preset = null;

    protected ?int $rootPageUid = null;

    public function __construct(private readonly SiteWriter $siteWriter, private readonly ConnectionPool $connectionPool, private readonly SiteFinder $siteFinder)
    {
    }

    public function createManualFromPreset(string $presetIdentifier): array
    {
        $this->preset = $this->getPresetByIdentifier($presetIdentifier);
        if (!$this->preset instanceof PresetInterface) {
            return [];
        }

        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->enableLogging = false;
        $dataHandler->bypassAccessCheckForRecords = true;
        $dataHandler->bypassWorkspaceRestrictions = true;
        $dataHandler->start($this->preset->getData(), []);
        $dataHandler->process_datamap();

        $this->rootPageUid = $dataHandler->substNEWwithIDs['NEW1'] ?? null;
        if (!$this->rootPageUid) {
            return [];
        }

        $this->createSiteConfiguration();

        return [
            'rootPageUid' => $this->rootPageUid,
        ];
    }

    protected function getPresetByIdentifier(string $presetIdentifier): ?PresetInterface
    {
        $pid = 0 - $this->getUidOfLastTopLevelPage();

        if ($presetIdentifier === '1') {
            return new EmptyManualPreset($pid);
        }

        return null;
    }

    private function getUidOfLastTopLevelPage(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $lastPage = $queryBuilder->select('uid')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)))
            ->orderBy('sorting', 'DESC')
            ->executeQuery()
            ->fetchOne();
        if (MathUtility::canBeInterpretedAsInteger($lastPage) && $lastPage > 0) {
            return (int)$lastPage;
        }
        return 0;
    }

    private function createSiteConfiguration(): void
    {
        $this->siteWriter->createNewBasicSite($this->getSiteIdentifier(), $this->rootPageUid, $this->getSiteBase());
        $siteFinder = $this->siteFinder;
        $site = $siteFinder->getSiteByPageId($this->rootPageUid);
        $siteConfiguration = $site->getConfiguration();
        $siteConfiguration['websiteTitle'] = $this->preset->getTitle();
        $this->siteWriter->write($this->getSiteIdentifier(), $siteConfiguration);
    }

    private function getSiteIdentifier(): string
    {
        $slug = $this->getSlugForSite();
        return $slug . '-' . $this->rootPageUid;
    }

    private function getSlugForSite(): string
    {
        return preg_replace('/[^a-z0-9]+/', '-', strtolower($this->preset->getTitle()));
    }

    private function getSiteBase(): string
    {
        return $this->getBaseDomain() . $this->getSlugForSite();
    }

    private function getBaseDomain(): string
    {
        $port = $GLOBALS['TYPO3_REQUEST']->getUri()->getPort() ? ':' . $GLOBALS['TYPO3_REQUEST']->getUri()->getPort() : '';
        return $GLOBALS['TYPO3_REQUEST']->getUri()->getScheme() . '://' . $GLOBALS['TYPO3_REQUEST']->getUri()->getHost() . $port . '/';
    }
}
