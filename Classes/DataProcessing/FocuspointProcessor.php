<?php

namespace Xima\XimaTypo3Manual\DataProcessing;

use Blueways\BwFocuspointImages\DataProcessing\FocuspointProcessor as BaseFocuspointProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FocuspointProcessor extends BaseFocuspointProcessor
{
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $processedData = parent::process($cObj, $contentObjectConfiguration, $processorConfiguration, $processedData);

        foreach ($processedData['points'] ?? [] as $points) {
            foreach ($points as $point) {
                if (($point->type ?? '') !== 'marking') {
                    continue;
                }
                $position = $point->markerPosition ?? ($point->x < 10 ? 'right' : 'left');
                [$point->markerCx, $point->markerCy, $point->markerTransform] = match ($position) {
                    'right'  => [$point->x + $point->width, $point->textY,             'translate(20, 0)'],
                    'top'    => [$point->textX,             $point->y,                 'translate(0, -20)'],
                    'bottom' => [$point->textX,             $point->y + $point->height, 'translate(0, 20)'],
                    'inside' => [$point->textX,             $point->textY,             'translate(0, 0)'],
                    default  => [$point->x,                 $point->textY,             'translate(-20, 0)'],
                };
            }
        }

        return $processedData;
    }
}
