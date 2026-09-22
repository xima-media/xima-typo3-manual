<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Generator;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Generator\ManualGenerator;
use Xima\XimaTypo3Manual\Generator\Preset\EmptyManualPreset;

final class ManualGeneratorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'blueways/bw-focuspoint-images',
        'blueways/bw-icons',
        'xima/xima-typo3-manual',
    ];

    protected array $coreExtensionsToLoad = [
        'rte_ckeditor',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Acceptance/Fixtures/be_users.csv');
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

        self::assertArrayHasKey('rootPageUid', $result);
        self::assertGreaterThan(0, $result['rootPageUid']);

        $row = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->select(['doktype', 'is_siteroot'], 'pages', ['uid' => $result['rootPageUid']])
            ->fetchAssociative();

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
