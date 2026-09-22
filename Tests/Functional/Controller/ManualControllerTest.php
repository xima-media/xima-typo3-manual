<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Controller;

use PHPUnit\Framework\Attributes\Test;
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
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
    }

    #[Test]
    public function hasManualRootPageReturnsTrueForManualRoot(): void
    {
        $result = ManualController::hasManualRootPage(3);
        self::assertTrue($result);
    }

    #[Test]
    public function hasManualRootPageReturnsFalseForRegularPage(): void
    {
        $result = ManualController::hasManualRootPage(1);
        self::assertFalse($result);
    }

    #[Test]
    public function getRootPageUidReturnsRootOfPageInManual(): void
    {
        $result = ManualController::getRootPageUid(4);
        self::assertSame(3, $result);
    }

    #[Test]
    public function getRootPageUidReturnsPageItselfForRoot(): void
    {
        $result = ManualController::getRootPageUid(3);
        self::assertSame(3, $result);
    }
}
