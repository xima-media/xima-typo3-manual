<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\EventListener;

use TYPO3\CMS\Backend\View\Event\IsContentUsedOnPageLayoutEvent;

final class ContentUsedOnPageEventListener
{
    public function __invoke(IsContentUsedOnPageLayoutEvent $event): void
    {
        $record = $event->getRecord();

        if ($record['tx_ximatypo3manual_parent']) {
            $event->setUsed(true);
        }
    }
}
