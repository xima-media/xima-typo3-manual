<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\EventListener;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XimaTypo3Manual\EventListener\ModifyButtonBarEventListener;
use Xima\XimaTypo3Manual\Tests\Functional\AbstractFunctionalTestCase;

final class ModifyButtonBarEventListenerTest extends AbstractFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tt_content.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Acceptance/Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
    }

    #[Test]
    public function aManualButtonIsAddedToThePageModule(): void
    {
        $buttons = $this->dispatch('/typo3/module/web/layout', ['id' => '1']);

        self::assertCount(1, $buttons['right'] ?? []);
    }

    #[Test]
    public function theManualModuleItselfGetsNoButton(): void
    {
        $buttons = $this->dispatch('/typo3/module/help/manual', ['id' => '3']);

        self::assertSame([], $buttons);
    }

    #[Test]
    public function aViewWithoutAPageAndWithoutARecordGetsNoButton(): void
    {
        $buttons = $this->dispatch('/typo3/module/web/list', []);

        self::assertSame([], $buttons);
    }

    /**
     * @param array<string, string> $queryParams
     * @return array<string, mixed>
     */
    private function dispatch(string $path, array $queryParams): array
    {
        $request = (new ServerRequest('https://example.com' . $path))->withQueryParams($queryParams);
        $request = $request
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
        // TYPO3 v13 reads the request from the globals, v14 from the event
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $event = new ModifyButtonBarEvent([], GeneralUtility::makeInstance(ButtonBar::class), $request);
        $this->get(ModifyButtonBarEventListener::class)($event);

        return $event->getButtons();
    }
}
