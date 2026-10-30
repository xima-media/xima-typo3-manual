<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Utility;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Reads the "id" and "language" parameters the manual module and its backend integrations are addressed with,
 * preferring the parsed body (POST) over query parameters, the way TYPO3 backend modules conventionally do.
 */
final class RequestParameterResolver
{
    public static function getPageId(ServerRequestInterface $request): int
    {
        $parsedBody = $request->getParsedBody();
        $id = (is_array($parsedBody) ? $parsedBody['id'] ?? null : null)
            ?? $request->getQueryParams()['id']
            ?? 0;

        return max(0, (int)$id);
    }

    public static function getLanguage(ServerRequestInterface $request): ?string
    {
        $parsedBody = $request->getParsedBody();
        $language = (is_array($parsedBody) ? $parsedBody['language'] ?? null : null)
            ?? $request->getQueryParams()['language']
            ?? null;

        return $language === null ? null : (string)$language;
    }
}
