<?php

declare(strict_types=1);

use Xima\XimaTypo3Manual\Controller\DownloadController;

return [
    'manual-download-pdf' => [
        'path' => '/XimaTypo3Manual/download',
        'access' => 'user,group',
        'target' => DownloadController::class . '::downloadPdf',
    ],
];
