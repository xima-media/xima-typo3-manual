<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\DataProcessing;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Hands the backend appearance settings to the manual, so it picks up the installation's logo and highlight colour.
 */
class BackendSettingsProcessor implements DataProcessorInterface
{
    public function __construct(
        private readonly AssetCollector $assetCollector,
        private readonly Typo3Version $typo3Version
    ) {
    }

    /**
     * @param mixed[] $contentObjectConfiguration
     * @param mixed[] $processorConfiguration
     * @param mixed[] $processedData
     * @return mixed[]
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $settings = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend'] ?? [];
        $processedData['backendSettings'] = $settings;

        $this->registerHighlightColor((string)($settings['loginHighlightColor'] ?? ''));

        return $processedData;
    }

    /**
     * The colour is configuration, not content, so it is emitted as an inline stylesheet. Registering it here rather
     * than in the template lets TYPO3 attach the nonce or hash a Content Security Policy needs.
     */
    private function registerHighlightColor(string $color): void
    {
        if (!preg_match('/^#[0-9a-f]{3,8}$/i', $color)) {
            return;
        }

        $this->assetCollector->addInlineStyleSheet(
            'manual-custom-link-color',
            ':root { --link-color: ' . $color . '; }',
            [],
            // TYPO3 v14 renamed the option, v13 only knows "useNonce"
            $this->typo3Version->getMajorVersion() >= 14 ? ['csp' => true] : ['useNonce' => true]
        );
    }
}
