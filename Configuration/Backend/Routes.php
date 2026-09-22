<?php

declare(strict_types=1);

use Xima\XimaTypo3Manual\Controller\DownloadController;

return [
    'manual-download-pdf' => [
        'path' => '/XimaTypo3ManualManual/download',
        'access' => 'public',
        'target' => DownloadController::class . '::downloadPdf',
    ],
];
