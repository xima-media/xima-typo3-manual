<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Service;

use PHPUnit\Framework\Attributes\Test;
use Xima\XimaTypo3Manual\Service\ManualRegistry;
use Xima\XimaTypo3Manual\Tests\Functional\AbstractFunctionalTestCase;

final class ManualRegistryTest extends AbstractFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Acceptance/Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
    }

    #[Test]
    public function onlyManualSiteRootsAreListed(): void
    {
        $manuals = $this->getRegistry()->getAccessibleManuals();

        self::assertCount(1, $manuals);
        self::assertSame(3, $manuals[0]->uid);
        self::assertSame('Manual', $manuals[0]->title);
    }

    #[Test]
    public function severalManualsAreListedInPageTreeOrder(): void
    {
        $this->createSecondManual();

        $manuals = $this->getRegistry()->getAccessibleManuals();

        self::assertSame([3, 100], array_map(static fn ($manual) => $manual->uid, $manuals));
        self::assertTrue($this->getRegistry()->hasSeveralManuals());
    }

    #[Test]
    public function aPageResolvesToTheManualItBelongsTo(): void
    {
        $this->createSecondManual();
        $registry = $this->getRegistry();

        self::assertSame(3, $registry->getManualRootForPage(6));
        self::assertSame(100, $registry->getManualRootForPage(101));
        // Page 1 is a regular page outside any manual
        self::assertSame(0, $registry->getManualRootForPage(1));
    }

    #[Test]
    public function theLastOpenedManualIsPreferredOnTheNextVisit(): void
    {
        $this->createSecondManual();
        $registry = $this->getRegistry();

        self::assertSame(3, $registry->getPreferredManualRoot());

        $registry->rememberManualRoot(100);

        self::assertSame(100, $this->getRegistry()->getPreferredManualRoot());
    }

    #[Test]
    public function aRememberedManualThatVanishedFallsBackToTheFirstOne(): void
    {
        $GLOBALS['BE_USER']->uc['xima_typo3_manual']['lastManual'] = 999;

        self::assertSame(3, $this->getRegistry()->getPreferredManualRoot());
    }

    private function createSecondManual(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        $connection->insert('pages', [
            'uid' => 100,
            'pid' => 0,
            'sorting' => 1024,
            'title' => 'Editor Handbook',
            'doktype' => 701,
            'is_siteroot' => 1,
            'slug' => '/handbook',
        ]);
        $connection->insert('pages', [
            'uid' => 101,
            'pid' => 100,
            'sorting' => 256,
            'title' => 'Working with news',
            'doktype' => 701,
            'slug' => '/handbook/news',
        ]);
    }

    private function getRegistry(): ManualRegistry
    {
        // A fresh instance, the registry caches the manuals it resolved
        return new ManualRegistry($this->getConnectionPool());
    }
}
