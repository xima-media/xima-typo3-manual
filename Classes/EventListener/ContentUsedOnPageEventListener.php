<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\EventListener;

use TYPO3\CMS\Backend\View\Event\IsContentUsedOnPageLayoutEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

/**
 * Steps of an msteps element live in their own colPos and are rendered by their parent, so the page module must not
 * list them again as unused content.
 */
final class ContentUsedOnPageEventListener
{
    #[AsEventListener(identifier: 'xima-typo3-manual/content-used-on-page')]
    public function __invoke(IsContentUsedOnPageLayoutEvent $event): void
    {
        if ((int)($event->getRecord()['tx_ximatypo3manual_parent'] ?? 0) > 0) {
            $event->setUsed(true);
        }
    }
}
