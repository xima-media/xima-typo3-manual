<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Tests\Functional;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Holds the extensions a manual needs, so a test class only declares what it tests.
 */
abstract class AbstractFunctionalTestCase extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'blueways/bw-focuspoint-images',
        'blueways/bw-icons',
        'xima/xima-typo3-manual',
    ];

    protected array $coreExtensionsToLoad = [
        'filelist',
        'indexed_search',
        'rte_ckeditor',
    ];
}
