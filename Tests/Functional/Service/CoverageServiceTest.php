<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Service;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Xima\XimaTypo3Manual\Service\CoverageService;

final class CoverageServiceTest extends FunctionalTestCase
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
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tt_content.csv');
    }

    #[Test]
    public function everyRecordTypeIsReportedAsUndocumentedForAnEmptyManual(): void
    {
        $coverage = $this->getCoverage(3);

        self::assertGreaterThan(0, $coverage['total']);
        self::assertSame(0, $coverage['documented']);
        self::assertCount($coverage['total'], $coverage['rows']);
    }

    #[Test]
    public function aChapterDocumentingAPageTypeIsReported(): void
    {
        $this->getConnectionPool()->getConnectionForTable('pages')
            ->update('pages', ['tx_ximatypo3manual_relations' => 'pages:1'], ['uid' => 4]);

        $coverage = $this->getCoverage(3);
        $row = $this->findRow($coverage['rows'], 'pages:1');

        self::assertSame(1, $coverage['documented']);
        self::assertSame('First Chapter', $row['documentedBy'][0]['title']);
        self::assertSame('pages', $row['documentedBy'][0]['table']);
    }

    #[Test]
    public function relationsOutsideTheManualAreIgnored(): void
    {
        // Page 1 is a regular page, not part of the manual page tree
        $this->getConnectionPool()->getConnectionForTable('pages')
            ->update('pages', ['tx_ximatypo3manual_relations' => 'pages:1'], ['uid' => 1]);

        self::assertSame(0, $this->getCoverage(3)['documented']);
    }

    #[Test]
    public function anElementOfAChapterDocumentsItsRecordType(): void
    {
        $this->getConnectionPool()->getConnectionForTable('tt_content')
            ->update('tt_content', ['tx_ximatypo3manual_relations' => 'tt_content:textmedia'], ['uid' => 1]);

        $row = $this->findRow($this->getCoverage(3)['rows'], 'tt_content:textmedia');

        self::assertSame('tt_content', $row['documentedBy'][0]['table']);
        self::assertSame(1, $row['documentedBy'][0]['uid']);
    }

    /**
     * @return array{rows: list<array<string, mixed>>, documented: int, total: int}
     */
    private function getCoverage(int $rootPageUid): array
    {
        return $this->get(CoverageService::class)->getCoverage($rootPageUid);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array<string, mixed>
     */
    private function findRow(array $rows, string $identifier): array
    {
        foreach ($rows as $row) {
            if ($row['recordType']->identifier === $identifier) {
                return $row;
            }
        }

        self::fail('No coverage row for "' . $identifier . '"');
    }
}
