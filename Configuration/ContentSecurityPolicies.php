<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceScheme;
use TYPO3\CMS\Core\Type\Map;

return Map::fromEntries(
    [
        Scope::frontend(),
        new MutationCollection(
            // The printable page type inlines every image as a data URI, see EncodeImagesBase64Middleware
            new Mutation(MutationMode::Extend, Directive::ImgSrc, SourceScheme::data),
        ),
    ],
    [
        Scope::backend(),
        new MutationCollection(
            // The manual is embedded as an iframe rendering the frontend of the same installation
            new Mutation(MutationMode::Extend, Directive::FrameSrc, SourceKeyword::self),
        ),
    ],
);
