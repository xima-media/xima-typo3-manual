<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Upgrades;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use Xima\XimaTypo3Manual\Configuration;

/**
 * Before version 2.0 annotation elements reused the CType of EXT:bw_focuspoint_images, which made them indistinguishable
 * from regular focus point images outside of a manual.
 */
#[UpgradeWizard('ximaManual_mAnnotationsUpgradeWizard')]
final readonly class MannotationsUpgradeWizard implements UpgradeWizardInterface
{
    private const LEGACY_CTYPE = 'bw_focuspoint_images_svg';

    public function __construct(private ConnectionPool $connectionPool)
    {
    }

    public function getTitle(): string
    {
        return 'Manual elements';
    }

    public function getDescription(): string
    {
        return 'Updates the CType of annotation elements inside manuals from "' . self::LEGACY_CTYPE . '" to "mannotation"';
    }

    public function executeUpdate(): bool
    {
        $elements = $this->getLegacyElementUids();
        if ($elements === []) {
            return true;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->update('tt_content')
            ->set('CType', 'mannotation')
            ->where(
                $queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($elements, Connection::PARAM_INT_ARRAY))
            )
            ->executeStatement();

        return true;
    }

    public function updateNecessary(): bool
    {
        return $this->getLegacyElementUids() !== [];
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    /**
     * @return list<int>
     */
    private function getLegacyElementUids(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $elements = $queryBuilder->select('c.uid')
            ->from('tt_content', 'c')
            ->innerJoin('c', 'pages', 'p', $queryBuilder->expr()->eq('c.pid', $queryBuilder->quoteIdentifier('p.uid')))
            ->where($queryBuilder->expr()->eq('p.doktype', $queryBuilder->createNamedParameter(Configuration::DOKTYPE_MANUAL, Connection::PARAM_INT)))
            ->andWhere($queryBuilder->expr()->eq('c.CType', $queryBuilder->createNamedParameter(self::LEGACY_CTYPE)))
            ->executeQuery()
            ->fetchFirstColumn();

        return array_map(intval(...), $elements);
    }
}
