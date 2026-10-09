<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\DataProcessing;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Hands the manual its own settings and the backend appearance settings, and turns the primary colour of the site
 * settings into the custom property the stylesheet is built on.
 */
class ManualSettingsProcessor implements DataProcessorInterface
{
    private const PRIMARY_COLOR_SETTING = 'manual.appearance.primaryColor';

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
        $processedData['backendSettings'] = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend'] ?? [];

        $primaryColor = $this->getPrimaryColor($cObj->getRequest());
        $processedData['primaryColor'] = $primaryColor;
        $this->registerPrimaryColor($primaryColor);

        return $processedData;
    }

    private function getPrimaryColor(ServerRequestInterface $request): string
    {
        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return '';
        }

        return (string)$site->getSettings()->get(self::PRIMARY_COLOR_SETTING);
    }

    /**
     * Emitted as an inline stylesheet rather than from the template, so TYPO3 can attach the nonce or hash a Content
     * Security Policy needs.
     */
    private function registerPrimaryColor(string $color): void
    {
        if (!preg_match('/^#[0-9a-f]{3,8}$/i', $color)) {
            return;
        }

        $this->assetCollector->addInlineStyleSheet(
            'manual-primary-color',
            ':root { --manual-primary: ' . $color . '; }',
            [],
            // TYPO3 v14 renamed the option, v13 only knows "useNonce"
            $this->typo3Version->getMajorVersion() >= 14 ? ['csp' => true] : ['useNonce' => true]
        );
    }
}
