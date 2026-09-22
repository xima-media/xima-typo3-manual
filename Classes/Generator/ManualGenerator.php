<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Generator;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Generator\Preset\PresetInterface;

class ManualGenerator
{
    /**
     * @var array<string, PresetInterface>
     */
    private array $presets = [];

    /**
     * @param iterable<PresetInterface> $presets
     */
    public function __construct(
        private readonly SiteWriter $siteWriter,
        private readonly ConnectionPool $connectionPool,
        private readonly SiteFinder $siteFinder,
        iterable $presets = []
    ) {
        foreach ($presets as $preset) {
            $this->presets[$preset->getIdentifier()] = $preset;
        }
    }

    /**
     * @return array<string, PresetInterface>
     */
    public function getAvailablePresets(): array
    {
        return $this->presets;
    }

    /**
     * @return array{rootPageUid?: int}
     */
    public function createManualFromPreset(string $presetIdentifier, ?ServerRequestInterface $request = null): array
    {
        $preset = $this->presets[$presetIdentifier] ?? null;
        if (!$preset instanceof PresetInterface) {
            return [];
        }

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->enableLogging = false;
        $dataHandler->bypassAccessCheckForRecords = true;
        // Removed in TYPO3 v14, the property is only present on v13
        if (isset(get_object_vars($dataHandler)['bypassWorkspaceRestrictions'])) {
            $dataHandler->bypassWorkspaceRestrictions = true;
        }
        $dataHandler->start($preset->getData(0 - $this->getUidOfLastTopLevelPage()), []);
        $dataHandler->process_datamap();

        $rootPageUid = (int)($dataHandler->substNEWwithIDs['NEW1'] ?? 0);
        if ($rootPageUid === 0) {
            return [];
        }

        $this->createSiteConfiguration($rootPageUid, $request);

        return [
            'rootPageUid' => $rootPageUid,
        ];
    }

    private function getUidOfLastTopLevelPage(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $lastPage = $queryBuilder->select('uid')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)))
            ->orderBy('sorting', 'DESC')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
        if (MathUtility::canBeInterpretedAsInteger($lastPage) && (int)$lastPage > 0) {
            return (int)$lastPage;
        }
        return 0;
    }

    private function createSiteConfiguration(int $rootPageUid, ?ServerRequestInterface $request): void
    {
        $identifier = 'manual-' . $rootPageUid;
        $this->siteWriter->createNewBasicSite($identifier, $rootPageUid, $this->getSiteBase($identifier, $request));

        $site = $this->siteFinder->getSiteByPageId($rootPageUid);
        $siteConfiguration = $site->getConfiguration();
        $siteConfiguration['websiteTitle'] = $this->getPageTitle($rootPageUid);
        $siteConfiguration['dependencies'] = array_values(array_unique(
            [...($siteConfiguration['dependencies'] ?? []), Configuration::SITE_SET]
        ));
        $this->siteWriter->write($identifier, $siteConfiguration);
    }

    private function getPageTitle(int $pageUid): string
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $title = $queryBuilder->select('title')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchOne();

        return is_string($title) ? $title : 'Manual';
    }

    private function getSiteBase(string $identifier, ?ServerRequestInterface $request): string
    {
        $request ??= $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return '/' . $identifier;
        }

        $uri = $request->getUri();
        $port = $uri->getPort() ? ':' . $uri->getPort() : '';

        return $uri->getScheme() . '://' . $uri->getHost() . $port . '/' . $identifier;
    }
}
