<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Security;

use TYPO3\CMS\Core\Context\Context;

final class BackendUserGroupAccessCheck
{
    public function groupAccessGranted(array $record, Context $context): bool
    {
        if ($record['doktype'] !== 701) {
            // Only check for manual pages
            return true;
        }

        if (!$record['tx_ximatypo3manual_begroup']) {
            // Anonymous access if no begroup restriction is set
            return true;
        }

        // No backend user, but 'tx_ximatypo3manual_begroup' is not empty, so shut this down.
        if (!$context->hasAspect('backend.user')) {
            return false;
        }

        // Access for admins allowed
        if ($context->getPropertyFromAspect('backend.user', 'isAdmin')) {
            return true;
        }

        $pageGroupList = explode(',', (string)$record['tx_ximatypo3manual_begroup']);
        return count(array_intersect($context->getAspect('backend.user')->getGroupIds(), $pageGroupList)) > 0;
    }
}

