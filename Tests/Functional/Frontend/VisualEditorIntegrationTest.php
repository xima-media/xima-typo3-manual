<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Frontend;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use Xima\XimaTypo3Manual\Tests\Functional\AbstractFunctionalTestCase;

/**
 * The editable regions themselves cannot be asserted here: they need a backend session, which a frontend sub request
 * of the testing framework does not carry. What is worth pinning is the opposite direction, that a reader of the
 * manual never sees any of the editor markup.
 */
final class VisualEditorIntegrationTest extends AbstractFunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'blueways/bw-focuspoint-images',
        'blueways/bw-icons',
        'friendsoftypo3/visual-editor',
        'xima/xima-typo3-manual',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (!ExtensionManagementUtility::isLoaded('visual_editor')) {
            self::markTestSkipped('EXT:visual_editor is an optional dependency and not installed');
        }

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tt_content.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Acceptance/Fixtures/be_users.csv');
        $this->createSiteConfiguration();
        $this->setUpFrontendRootPage(3, ['EXT:xima_typo3_manual/Configuration/Sets/Manual/setup.typoscript']);
    }

    #[Test]
    public function theManualCarriesNoEditorMarkupForReaders(): void
    {
        $html = (string)$this->executeFrontendSubRequest(new InternalRequest('https://example.com/'))->getBody();

        self::assertStringNotContainsString('ve-editable', $html);
        self::assertStringNotContainsString('data-veedit', $html);
    }

    private function createSiteConfiguration(): void
    {
        $path = Environment::getConfigPath() . '/sites/manual';
        GeneralUtility::mkdir_deep($path);
        file_put_contents($path . '/config.yaml', <<<'YAML'
            rootPageId: 3
            base: 'https://example.com/'
            languages:
              -
                title: English
                enabled: true
                languageId: 0
                base: /
                locale: en_US.UTF-8
            YAML);
    }
}
