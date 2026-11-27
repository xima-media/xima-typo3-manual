<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Controller;

use PHPUnit\Framework\Attributes\Test;
use Xima\XimaTypo3Manual\Controller\ManualController;
use Xima\XimaTypo3Manual\Tests\Functional\AbstractFunctionalTestCase;

final class ManualControllerTest extends AbstractFunctionalTestCase
{
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
