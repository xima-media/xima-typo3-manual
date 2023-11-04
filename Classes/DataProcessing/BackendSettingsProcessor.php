<?php

namespace Xima\XimaTypo3Manual\DataProcessing;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class BackendSettingsProcessor implements DataProcessorInterface
{

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
        return $processedData;
    }
}
