<?php

namespace Xima\XimaTypo3Manual\EventListener;

use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;
use Xima\XimaTypo3Manual\Service\ContentParser;

final class AfterCacheableContentIsGeneratedEventListener
{
    public function __construct(protected ContentParser $contentParser)
    {
    }

    public function __invoke(AfterCacheableContentIsGeneratedEvent $event): void
    {
        if ($event->getController()->page['doktype'] === 701) {
            $event->getController()->content = $this->contentParser->process(
                $event->getController()->content,
            );
        }
    }
}
