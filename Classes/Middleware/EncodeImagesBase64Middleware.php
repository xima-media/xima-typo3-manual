<?php

namespace Xima\XimaTypo3Manual\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SimpleXMLElement;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class EncodeImagesBase64Middleware implements MiddlewareInterface
{
    /**
    * @var array<string, string>
    */
    protected array $svgSpriteCache = [];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $params = $request->getQueryParams();
        if (
            !($response instanceof NullResponse)
            && $GLOBALS['TSFE'] instanceof TypoScriptFrontendController
            && isset($params['type'])
            && $params['type'] === '1664618986'
        ) {
            $body = $response->getBody();
            $body->rewind();
            $contents = $response->getBody()->getContents();
            $content = $this->parseImageUrlsAndEncodeBase64($contents);
            $content = $this->convertSvgsToImage($content);
            $body = new Stream('php://temp', 'rw');
            $body->write($content);
            $response = $response->withBody($body);
        }

        return $response;
    }

    protected function parseImageUrlsAndEncodeBase64(string $input = ''): string
    {
        $pattern = '/<img[^>]+src="([^">]+)"/';

        return preg_replace_callback($pattern, static function ($img): string {
            if (!is_array($img)) {
                return '';
            }
            $path = Environment::getPublicPath();
            $path .= str_starts_with($img[1], '/') ? $img[1] : '/' . $img[1];
            if (!file_exists($path)) {
                return $img[0];
            }
            $fileContent = GeneralUtility::getUrl($path);
            if (!$fileContent) {
                return $img[0];
            }

            $fileType = mime_content_type($path);
            $newSrc = 'data:' . $fileType . ';base64,' . base64_encode($fileContent);
            return str_replace($img[1], $newSrc, $img[0]);
        }, $input) ?? '';
    }

    protected function convertSvgsToImage(string $input = ''): string
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML($input);
        $svgs = $doc->getElementsByTagName('svg');

        /** @var \DOMNode $svg */
        foreach ($svgs as $svg) {
            $svgMarkup = $svg->ownerDocument->saveHTML($svg);
            $this->resolveSvgSprite($svgMarkup);
            $img = $doc->createElement('img', '');
            $img->setAttribute('src', 'data:image/svg+xml;base64,' . base64_encode($svgMarkup));
            $svg->parentNode->insertBefore($img);
        }
        return $doc->saveHTML() ?: '';
    }

    protected function resolveSvgSprite(string &$svgMarkup): void
    {
        $pattern = '/<use[^>]+xlink:href="([^">]+)"/';
        if (!preg_match_all($pattern, $svgMarkup, $svgParts)) {
            return;
        }

        $urlParts = GeneralUtility::trimExplode('#', $svgParts[1][0] ?? '');
        if (count($urlParts) !== 2) {
            return;
        }

        // resolve sprite url, cache requests
        $spriteUrl = Environment::getPublicPath() . '/' . $urlParts[0];
        $spriteUrlHash = md5($spriteUrl);
        $spriteMarkup = $this->svgSpriteCache[$spriteUrlHash] ?? GeneralUtility::getUrl($spriteUrl);
        $this->svgSpriteCache[$spriteUrlHash] = $spriteMarkup;
        if (!$spriteMarkup) {
            return;
        }

        // parse sprite markup for icon id
        $svg = new SimpleXMLElement($spriteMarkup);
        $namespaces = $svg->getDocNamespaces() ?: [''];
        $svg->registerXPathNamespace('__nons', $namespaces['']);
        $searchResult = $svg->xpath('//__nons:symbol[@id="' . $urlParts[1] . '"]');
        if (!isset($searchResult[0]) || !$searchResult[0] instanceof SimpleXMLElement) {
            return;
        }

        // convert to back to string
        $iconMarkup = $searchResult[0]->asXML();
        if (!is_string($iconMarkup)) {
            return;
        }

        $svgMarkup = $iconMarkup;
    }
}
