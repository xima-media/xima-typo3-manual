<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Upgrades;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('ximaManual_mAnnotationsUpgradeWizard')]
final class MannotationsUpgradeWizard implements UpgradeWizardInterface
{
    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }
    public function getTitle(): string
    {
        return 'Manual elements';
    }

    public function getDescription(): string
    {
        return 'Updates the CType of annotation elements';
    }

    public function executeUpdate(): bool
    {
        $elements = $this->getLegacyElementUids();

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->update('tt_content')
            ->set('CType', 'mannotation')
            ->where(
                $queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($elements, Connection::PARAM_INT_ARRAY))
            )
            ->executeStatement();

        return true;
    }

    private function getLegacyElementUids(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $elements = $queryBuilder->select('c.uid')
            ->from('tt_content', 'c')
            ->innerJoin('c', 'pages', 'p', $queryBuilder->expr()->eq('c.pid', $queryBuilder->quoteIdentifier('p.uid')))
            ->where($queryBuilder->expr()->eq('p.doktype', $queryBuilder->createNamedParameter('701', Connection::PARAM_INT)))
            ->andWhere($queryBuilder->expr()->eq('c.CType', $queryBuilder->createNamedParameter('bw_focuspoint_images_svg')))
            ->executeQuery()
            ->fetchAllAssociativeIndexed();

        return array_keys($elements);
    }

    public function updateNecessary(): bool
    {
        $elements = $this->getLegacyElementUids();
        return count($elements) > 0;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
