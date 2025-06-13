<?php

namespace Xima\XimaTypo3Manual\Generator;

use TYPO3\CMS\Core\Configuration\SiteConfiguration;
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

    public function __construct(private ?SiteWriter $siteWriter)
    {
    }

    public function createManualFromPreset(string $presetIdentifier): array
    {
        $this->preset = $this->getPresetByIdentifier($presetIdentifier);
        if (!$this->preset) {
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

        if ($this->siteWriter) {
            $this->createSiteConfiguration();
        } else {
            $this->createSiteConfigurationV12();
        }

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

    private function createSiteConfiguration(): void
    {
        $this->siteWriter->createNewBasicSite($this->getSiteIdentifier(), $this->rootPageUid, $this->getSiteBase());
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
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

    private function createSiteConfigurationV12(): void
    {
        $siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
        $configuration = [
            'base' => $this->getSiteBase(),
            'rootPageId' => $this->rootPageUid,
            'routes' => [],
            'websiteTitle' => $this->preset->getTitle(),
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
        $siteConfiguration->write($this->getSiteIdentifier(), $configuration);
    }
}
