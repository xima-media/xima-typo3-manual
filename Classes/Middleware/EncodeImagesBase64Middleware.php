<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Middleware;

use DOMDocument;
use DOMNode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SimpleXMLElement;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XimaTypo3Manual\Configuration;

/**
 * Inlines every image and SVG of the printable manual page type, because dompdf renders the HTML without access to the
 * TYPO3 instance and therefore cannot resolve relative asset URLs or SVG sprites.
 */
final class EncodeImagesBase64Middleware implements MiddlewareInterface
{
    /**
     * @var array<string, string|false>
     */
    private array $svgSpriteCache = [];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($response instanceof NullResponse || !$this->isPdfRequest($request)) {
            return $response;
        }

        $body = $response->getBody();
        $body->rewind();
        $content = $this->parseImageUrlsAndEncodeBase64($body->getContents());
        $content = $this->convertSvgsToImage($content);

        $newBody = new Stream('php://temp', 'rw');
        $newBody->write($content);

        return $response->withBody($newBody);
    }

    private function isPdfRequest(ServerRequestInterface $request): bool
    {
        $pageArguments = $request->getAttribute('routing');
        if ($pageArguments instanceof PageArguments) {
            return (int)$pageArguments->getPageType() === Configuration::PDF_PAGE_TYPE;
        }

        return (int)($request->getQueryParams()['type'] ?? 0) === Configuration::PDF_PAGE_TYPE;
    }

    private function parseImageUrlsAndEncodeBase64(string $input): string
    {
        $pattern = '/<img[^>]+src="([^">]+)"/';

        return preg_replace_callback($pattern, function (array $img): string {
            $source = $img[1];
            if (str_starts_with($source, 'data:')) {
                return $img[0];
            }

            $path = $this->resolveLocalPath($source);
            if ($path === null) {
                return $img[0];
            }

            $fileContent = file_get_contents($path);
            if ($fileContent === false || $fileContent === '') {
                return $img[0];
            }

            $newSrc = 'data:' . (mime_content_type($path) ?: 'application/octet-stream') . ';base64,' . base64_encode($fileContent);
            return str_replace($source, $newSrc, $img[0]);
        }, $input) ?? $input;
    }

    private function convertSvgsToImage(string $input): string
    {
        if (!str_contains($input, '<svg')) {
            return $input;
        }

        $doc = new DOMDocument();
        if (!@$doc->loadHTML($input, LIBXML_NOERROR | LIBXML_NOWARNING)) {
            return $input;
        }

        // The node list is live, so it has to be materialised before the tree is modified
        $svgs = iterator_to_array($doc->getElementsByTagName('svg'));

        foreach ($svgs as $svg) {
            if (!$svg->parentNode instanceof DOMNode) {
                continue;
            }

            $svgMarkup = $doc->saveHTML($svg);
            if ($svgMarkup === false) {
                continue;
            }

            $svgMarkup = $this->resolveSvgSprite($svgMarkup);
            $img = $doc->createElement('img');
            $img->setAttribute('src', 'data:image/svg+xml;base64,' . base64_encode($svgMarkup));
            foreach (['class', 'width', 'height'] as $attribute) {
                if ($svg->hasAttribute($attribute)) {
                    $img->setAttribute($attribute, $svg->getAttribute($attribute));
                }
            }

            $svg->parentNode->replaceChild($img, $svg);
        }

        return $doc->saveHTML() ?: $input;
    }

    /**
     * An icon referenced from an SVG sprite (<use xlink:href="sprite.svg#icon">) carries no drawing instructions of its
     * own, so the referenced symbol is pulled out of the sprite and returned as a standalone SVG.
     */
    private function resolveSvgSprite(string $svgMarkup): string
    {
        $pattern = '/<use[^>]+(?:xlink:)?href="([^">]+)"/';
        if (!preg_match($pattern, $svgMarkup, $matches)) {
            return $svgMarkup;
        }

        $urlParts = GeneralUtility::trimExplode('#', $matches[1]);
        if (count($urlParts) !== 2) {
            return $svgMarkup;
        }

        $spritePath = $this->resolveLocalPath($urlParts[0]);
        if ($spritePath === null) {
            return $svgMarkup;
        }

        $spriteMarkup = $this->svgSpriteCache[$spritePath] ??= file_get_contents($spritePath);
        if (!is_string($spriteMarkup) || $spriteMarkup === '') {
            return $svgMarkup;
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $sprite = new SimpleXMLElement($spriteMarkup);
        } catch (\Exception) {
            return $svgMarkup;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $namespaces = $sprite->getDocNamespaces();
        $sprite->registerXPathNamespace('__nons', $namespaces[''] ?? 'http://www.w3.org/2000/svg');
        $searchResult = $sprite->xpath('//__nons:symbol[@id="' . $urlParts[1] . '"]');
        if (!isset($searchResult[0])) {
            return $svgMarkup;
        }

        $iconMarkup = $searchResult[0]->asXML();

        return is_string($iconMarkup) ? $iconMarkup : $svgMarkup;
    }

    /**
     * Maps a public URL onto a file below the public path, refusing anything that escapes it.
     */
    private function resolveLocalPath(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return null;
        }

        $publicPath = Environment::getPublicPath();
        $path = realpath($publicPath . '/' . ltrim(rawurldecode($path), '/'));

        if ($path === false || !str_starts_with($path, $publicPath) || !is_file($path)) {
            return null;
        }

        return $path;
    }
}
