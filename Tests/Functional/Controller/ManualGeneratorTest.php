<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Xima\XimaTypo3Manual\Controller\DownloadController;
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
        'dashboard',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Acceptance/Fixtures/be_users.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/sys_template.csv');
    }

    /**
     * @test
     */
    public function manualGeneratorCreatesManualWithPreset(): void
    {
        $siteWriter = GeneralUtility::makeInstance(SiteWriter::class);
        $generator = new ManualGenerator($siteWriter);

        $result = $generator->createManualFromPreset('1');

        self::assertArrayHasKey('rootPageUid', $result);
        self::assertGreaterThan(0, $result['rootPageUid']);
    }

    /**
     * @test
     */
    public function manualGeneratorReturnsEmptyForInvalidPreset(): void
    {
        $siteWriter = GeneralUtility::makeInstance(SiteWriter::class);
        $generator = new ManualGenerator($siteWriter);

        $result = $generator->createManualFromPreset('invalid');

        self::assertEmpty($result);
    }
}
