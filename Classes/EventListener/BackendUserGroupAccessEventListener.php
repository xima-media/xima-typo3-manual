<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\EventListener;

use TYPO3\CMS\Core\Domain\Access\RecordAccessGrantedEvent;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use Xima\XimaTypo3Manual\Security\BackendUserGroupAccessCheck;

final class BackendUserGroupAccessEventListener
{
    public function __construct(protected PageRepository $pageRepository, protected BackendUserGroupAccessCheck $check) {}
    public function __invoke(RecordAccessGrantedEvent $event): void
    {
        // ToDo: Why only get a part of the full page record?
        $fullPageRecord = $this->pageRepository->getPage($event->getRecord()['uid']);
        $event->setAccessGranted($this->check->groupAccessGranted($fullPageRecord, $event->getContext()));
    }
}

