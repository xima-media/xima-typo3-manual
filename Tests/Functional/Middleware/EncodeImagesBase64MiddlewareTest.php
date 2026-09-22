<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional\Middleware;

use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Middleware\EncodeImagesBase64Middleware;

final class EncodeImagesBase64MiddlewareTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'blueways/bw-focuspoint-images',
        'blueways/bw-icons',
        'xima/xima-typo3-manual',
    ];

    protected array $coreExtensionsToLoad = [
        'rte_ckeditor',
    ];

    private const IMAGE_URL = '/fileadmin/middleware-test.svg';

    protected function setUp(): void
    {
        parent::setUp();

        $path = Environment::getPublicPath() . self::IMAGE_URL;
        GeneralUtility::mkdir_deep(dirname($path));
        file_put_contents($path, '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>');
    }

    #[Test]
    public function regularPageTypesAreLeftUntouched(): void
    {
        $html = '<img src="' . self::IMAGE_URL . '">';

        self::assertSame($html, $this->process($html, 0));
    }

    #[Test]
    public function imagesOfThePdfPageTypeAreInlined(): void
    {
        $result = $this->process('<img src="' . self::IMAGE_URL . '">', Configuration::PDF_PAGE_TYPE);

        self::assertStringContainsString('src="data:image/svg+xml;base64,', $result);
    }

    #[Test]
    public function inlineSvgIsReplacedByAnImageInsteadOfBeingDuplicated(): void
    {
        $result = $this->process('<p><svg class="icon"><path d="M0 0"/></svg></p>', Configuration::PDF_PAGE_TYPE);

        self::assertStringNotContainsString('<svg', $result);
        self::assertStringContainsString('<img', $result);
        self::assertStringContainsString('class="icon"', $result);
    }

    #[Test]
    public function filesOutsideThePublicPathAreNotInlined(): void
    {
        $result = $this->process('<img src="/../../etc/passwd">', Configuration::PDF_PAGE_TYPE);

        self::assertStringNotContainsString('data:', $result);
    }

    private function process(string $html, int $pageType): string
    {
        $request = (new ServerRequest('https://example.com/manual/'))
            ->withAttribute('routing', new PageArguments(1, (string)$pageType, []));

        $handler = new class($html) implements RequestHandlerInterface {
            public function __construct(private readonly string $html)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new HtmlResponse($this->html);
            }
        };

        $response = (new EncodeImagesBase64Middleware())->process($request, $handler);
        $response->getBody()->rewind();

        return $response->getBody()->getContents();
    }
}
