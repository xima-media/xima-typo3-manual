<?php

use Xima\XimaTypo3Manual\Middleware\EncodeImagesBase64Middleware;

return [
    'frontend' => [
        'xima/absolute-paths' => [
            'target' => EncodeImagesBase64Middleware::class,
            'after' => [
                'typo3/cms-frontend/output-compression',
            ],
        ],
    ],
];
