<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Domain\Manual;

/**
 * An installation can hold any number of manuals, one per page tree with a manual page as its site root. This service
 * is the single place that knows which of them the current backend user may read and which one they last looked at.
 */
class ManualRegistry
{
    private const USER_SETTING = 'xima_typo3_manual';

    /**
     * @var list<Manual>|null
     */
    private ?array $manuals = null;

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }

    /**
     * @return list<Manual>
     */
    public function getAccessibleManuals(): array
    {
        if ($this->manuals !== null) {
            return $this->manuals;
        }

        $qb = $this->connectionPool->getQueryBuilderForTable('pages');
        $rows = $qb->select('uid', 'title')
            ->from('pages')
            ->where(
                $qb->expr()->eq('doktype', $qb->createNamedParameter(Configuration::DOKTYPE_MANUAL, Connection::PARAM_INT)),
                $qb->expr()->eq('is_siteroot', $qb->createNamedParameter(1, Connection::PARAM_INT))
            )
            ->orderBy('sorting', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        $manuals = [];
        $permsClause = $this->getBackendUser()?->getPagePermsClause(Permission::PAGE_SHOW) ?? '1=1';
        foreach ($rows as $row) {
            if (BackendUtility::readPageAccess((int)$row['uid'], $permsClause) === false) {
                continue;
            }
            $manuals[] = new Manual((int)$row['uid'], (string)($row['title'] ?? ''));
        }

        return $this->manuals = $manuals;
    }

    public function hasSeveralManuals(): bool
    {
        return count($this->getAccessibleManuals()) > 1;
    }

    public function isAccessible(int $rootPageUid): bool
    {
        foreach ($this->getAccessibleManuals() as $manual) {
            if ($manual->uid === $rootPageUid) {
                return true;
            }
        }

        return false;
    }

    public function getTitle(int $rootPageUid): string
    {
        foreach ($this->getAccessibleManuals() as $manual) {
            if ($manual->uid === $rootPageUid) {
                return $manual->title;
            }
        }

        return '';
    }

    /**
     * The manual the module should open when it is called without a page: the one the user read last, falling back to
     * the first they may read.
     */
    public function getPreferredManualRoot(): int
    {
        $remembered = (int)($this->getBackendUser()?->uc[self::USER_SETTING]['lastManual'] ?? 0);
        if ($remembered > 0 && $this->isAccessible($remembered)) {
            return $remembered;
        }

        return $this->getAccessibleManuals()[0]->uid ?? 0;
    }

    public function rememberManualRoot(int $rootPageUid): void
    {
        $backendUser = $this->getBackendUser();
        if (!$backendUser instanceof BackendUserAuthentication || $rootPageUid <= 0) {
            return;
        }

        if ((int)($backendUser->uc[self::USER_SETTING]['lastManual'] ?? 0) === $rootPageUid) {
            return;
        }

        $backendUser->uc[self::USER_SETTING]['lastManual'] = $rootPageUid;
        $backendUser->writeUC();
    }

    /**
     * Resolves the manual a page belongs to, regardless of how deep inside the tree the page sits.
     */
    public function getManualRootForPage(int $pageUid): int
    {
        if ($pageUid <= 0) {
            return 0;
        }

        $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        if ((int)($rootline[0]['doktype'] ?? 0) !== Configuration::DOKTYPE_MANUAL) {
            return 0;
        }

        return (int)($rootline[0]['uid'] ?? 0);
    }

    private function getBackendUser(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'] ?? null;
    }
}
