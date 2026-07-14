<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Xima\XimaTypo3Manual\Controller\ManualController;

final class ManualControllerTest extends FunctionalTestCase
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
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tt_content.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/sys_template.csv');

        $this->setUpFrontendRootPage(3, ['EXT:xima_typo3_manual/Configuration/TypoScript']);
    }

    /**
     * @test
     */
    public function hasManualRootPageReturnsTrueForManualRoot(): void
    {
        $result = ManualController::hasManualRootPage(3);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function hasManualRootPageReturnsFalseForRegularPage(): void
    {
        $result = ManualController::hasManualRootPage(1);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function getRootPageUidReturnsRootOfPageInManual(): void
    {
        $result = ManualController::getRootPageUid(4);
        self::assertSame(3, $result);
    }

    /**
     * @test
     */
    public function getRootPageUidReturnsPageItselfForRoot(): void
    {
        $result = ManualController::getRootPageUid(3);
        self::assertSame(3, $result);
    }
}
