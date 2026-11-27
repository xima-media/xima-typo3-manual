<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Generator;

use PHPUnit\Framework\Attributes\Test;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Generator\ManualGenerator;
use Xima\XimaTypo3Manual\Generator\Preset\EmptyManualPreset;
use Xima\XimaTypo3Manual\Tests\Functional\AbstractFunctionalTestCase;

final class ManualGeneratorTest extends AbstractFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
    }

    #[Test]
    public function availablePresetsContainTheEmptyManual(): void
    {
        $presets = $this->getGenerator()->getAvailablePresets();

        self::assertArrayHasKey('empty', $presets);
        self::assertInstanceOf(EmptyManualPreset::class, $presets['empty']);
    }

    #[Test]
    public function manualGeneratorCreatesManualWithPreset(): void
    {
        $result = $this->getGenerator()->createManualFromPreset('empty');
        $rootPageUid = $result['rootPageUid'] ?? 0;

        self::assertGreaterThan(0, $rootPageUid);

        $row = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->select(['doktype', 'is_siteroot'], 'pages', ['uid' => $rootPageUid])
            ->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame(Configuration::DOKTYPE_MANUAL, (int)$row['doktype']);
        self::assertSame(1, (int)$row['is_siteroot']);
    }

    #[Test]
    public function manualGeneratorReturnsEmptyForInvalidPreset(): void
    {
        self::assertSame([], $this->getGenerator()->createManualFromPreset('invalid'));
    }

    private function getGenerator(): ManualGenerator
    {
        return $this->get(ManualGenerator::class);
    }
}
