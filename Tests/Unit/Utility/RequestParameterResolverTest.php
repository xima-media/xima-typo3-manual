<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Unit\Utility;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Xima\XimaTypo3Manual\Utility\RequestParameterResolver;

final class RequestParameterResolverTest extends UnitTestCase
{
    /**
     * @return array<string, array{array<string, mixed>|null, array<string, mixed>, int}>
     */
    public static function pageIdDataProvider(): array
    {
        return [
            'no parameter' => [null, [], 0],
            'query parameter' => [null, ['id' => '12'], 12],
            'parsed body wins over query' => [['id' => '7'], ['id' => '12'], 7],
            'folder identifier of the file list' => [null, ['id' => '1:/user_upload/'], 0],
            'negative uid' => [null, ['id' => '-3'], 0],
        ];
    }

    /**
     * @param array<string, mixed>|null $parsedBody
     * @param array<string, mixed> $queryParams
     */
    #[Test]
    #[DataProvider('pageIdDataProvider')]
    public function getPageIdReadsThePageUid(?array $parsedBody, array $queryParams, int $expected): void
    {
        $request = (new ServerRequest('https://example.com/typo3/'))
            ->withParsedBody($parsedBody)
            ->withQueryParams($queryParams);

        self::assertSame($expected, RequestParameterResolver::getPageId($request));
    }

    #[Test]
    public function getLanguagePrefersTheParsedBody(): void
    {
        $request = (new ServerRequest('https://example.com/typo3/'))
            ->withParsedBody(['language' => 1])
            ->withQueryParams(['language' => '2']);

        self::assertSame('1', RequestParameterResolver::getLanguage($request));
    }

    #[Test]
    public function getLanguageReturnsNullWithoutParameter(): void
    {
        self::assertNull(RequestParameterResolver::getLanguage(new ServerRequest('https://example.com/typo3/')));
    }
}
