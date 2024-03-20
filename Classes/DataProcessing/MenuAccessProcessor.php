<?php

namespace Xima\XimaTypo3Manual\DataProcessing;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use Xima\XimaTypo3Manual\Security\BackendUserGroupAccessCheck;

class MenuAccessProcessor implements DataProcessorInterface
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
        $accessCheck = GeneralUtility::makeInstance(BackendUserGroupAccessCheck::class);
        $context = GeneralUtility::makeInstance(Context::class);

        foreach ($processedData['pages'] as $key => $page) {
            if ($accessCheck->groupAccessGranted($page['data'], $context)) {
                continue;
            }
            unset($processedData['pages'][$key]);
        }
        return $processedData;
    }
}
