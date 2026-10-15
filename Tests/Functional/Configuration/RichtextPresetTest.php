<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Configuration;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Configuration\Richtext;
use Xima\XimaTypo3Manual\Tests\Functional\AbstractFunctionalTestCase;

final class RichtextPresetTest extends AbstractFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
    }

    /**
     * EXT:visual_editor reads every entry as an array with its exports, and TYPO3 documents that form. A plain
     * string passes unnoticed in the backend but breaks the frontend editor, so the preset is pinned to it.
     */
    #[Test]
    public function everyImportModuleOfThePresetIsDeclaredWithItsExports(): void
    {
        $importModules = $this->resolveConfiguration()['editor']['config']['importModules'] ?? [];

        self::assertNotSame([], $importModules);
        foreach ($importModules as $importModule) {
            self::assertIsArray($importModule, 'Import module "' . var_export($importModule, true) . '" is not an array');
            self::assertArrayHasKey('module', $importModule);
            // The CKEditor bootstrap iterates the exports of every entry, a missing key aborts the whole editor
            self::assertArrayHasKey('exports', $importModule, 'Import module "' . $importModule['module'] . '" has no exports');
            self::assertNotSame([], $importModule['exports']);
        }
    }

    #[Test]
    public function thePresetKeepsTheImportModulesOfTheCoreAndOfTheIconPicker(): void
    {
        $modules = array_column($this->resolveConfiguration()['editor']['config']['importModules'] ?? [], 'module');

        self::assertContains('@typo3/rte-ckeditor/plugin/whitespace.js', $modules);
        self::assertContains('@typo3/rte-ckeditor/plugin/typo3-link.js', $modules);
        self::assertContains('@blueways/bw-icons/IconPicker.js', $modules);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveConfiguration(): array
    {
        return $this->get(Richtext::class)->getConfiguration(
            'tt_content',
            'bodytext',
            3,
            'mtext',
            ['richtextConfiguration' => 'xima_typo3_manual']
        );
    }
}
