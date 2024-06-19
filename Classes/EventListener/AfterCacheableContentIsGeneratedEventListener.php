<?php

namespace Xima\XimaTypo3Manual\EventListener;

use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;
use Xima\XimaTypo3Manual\Service\ContentParser;

final class AfterCacheableContentIsGeneratedEventListener
{
    public function __construct(protected ContentParser $contentParser)
    {
    }

    /**
     * @throws \DOMException
     */
    public function __invoke(AfterCacheableContentIsGeneratedEvent $event): void
    {
        if ($event->getController()->page['doktype'] === 701 && !$this->parserIsDisabled()) {
            $event->getController()->content = $this->contentParser->process(
                $event->getController()->content,
            );
        }
    }

    private function parserIsDisabled(): bool
    {
        $setup = $GLOBALS['TSFE']->tmpl->setup;
        $extensionConfiguration = $setup['plugin.']['tx_ximatypo3manual.'];
        $settings = $extensionConfiguration['settings.'];
        return ($settings['disableGlossaryTermParser'] ?? false);
    }
}
