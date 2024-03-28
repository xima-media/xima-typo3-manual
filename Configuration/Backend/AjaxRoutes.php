<?php

use Xima\XimaTypo3Manual\Controller\InstallationController;

return [
    'manual_installation_create' => [
        'path' => '/XimaTypo3Manual/install',
        'access' => 'user,group',
        'target' => InstallationController::class . '::installPreset',
    ],
];
