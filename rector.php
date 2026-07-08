<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\ValueObject\PhpVersion;
use Ssch\TYPO3Rector\CodeQuality\General\ConvertImplicitVariablesToExplicitGlobalsRector;
use Ssch\TYPO3Rector\CodeQuality\General\ExtEmConfRector;
use Ssch\TYPO3Rector\CodeQuality\General\RemoveTypo3VersionChecksRector;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/Classes',
        __DIR__ . '/Configuration',
        __DIR__ . '/ext_emconf.php',
        __DIR__ . '/ext_localconf.php',
        __DIR__ . '/ext_tables.php',
    ])
    // uncomment to reach your current PHP version
    // ->withPhpSets()
    ->withPhpVersion(PhpVersion::PHP_82)
    ->withSets([
        Typo3SetList::CODE_QUALITY,
        Typo3SetList::GENERAL,
        Typo3LevelSetList::UP_TO_TYPO3_12,
    ])
    // To have a better analysis from PHPStan, we teach it here some more things
    ->withPHPStanConfigs([Typo3Option::PHPSTAN_FOR_RECTOR_PATH])
    ->withImportNames(true, true, false, true)
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
        ConvertImplicitVariablesToExplicitGlobalsRector::class,
    ])
    ->withConfiguredRule(ExtEmConfRector::class, [
        ExtEmConfRector::PHP_VERSION_CONSTRAINT => '8.2.0-8.4.99',
        ExtEmConfRector::TYPO3_VERSION_CONSTRAINT => '12.0.0-13.4.99',
        ExtEmConfRector::ADDITIONAL_VALUES_TO_BE_REMOVED => [],
    ])
    ->withConfiguredRule(
        RemoveTypo3VersionChecksRector::class,
        [RemoveTypo3VersionChecksRector::TARGET_VERSION => 12]
    )
    ->withSkip([
        // @see https://github.com/sabbelasichon/typo3-rector/issues/2536
        __DIR__ . '/**/Configuration/ExtensionBuilder/*',
        NameImportingPostRector::class => [
            'ClassAliasMap.php',
        ],
    ])
;
