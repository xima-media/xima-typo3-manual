<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Widgets\Provider;

use Throwable;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Dashboard\Widgets\ButtonProviderInterface;

readonly class ManualButtonProvider implements ButtonProviderInterface
{
    public function __construct(
        private UriBuilder $uriBuilder,
        private int $pageId,
        private string $title,
        private string $context = 'iframe',
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        try {
            return (string)$this->uriBuilder->buildUriFromRoute(
                'xima_typo3_manual',
                ['id' => $this->pageId, 'context' => $this->context, 'language' => $GLOBALS['BE_USER']->uc['lang'] ?? '']
            );
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): string
    {
        return '';
    }
}
