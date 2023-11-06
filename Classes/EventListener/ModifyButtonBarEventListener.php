<?php

namespace Xima\XimaTypo3Manual\EventListener;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;

final readonly class ModifyButtonBarEventListener
{
    public function __construct(private IconFactory $iconFactory, private UriBuilder $uriBuilder)
    {
    }

    public function __invoke(ModifyButtonBarEvent $event): void
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $pageId = (int)($request->getParsedBody()['id'] ?? $request->getQueryParams()['id'] ?? 0);

        if (!$pageId) {
            return;
        }

        $uri = $request->getUri();
        if (str_contains($uri, 'help/manual')) {
            return;
        }

        $manualButton = $event->getButtonBar()->makeLinkButton();
        $manualButton->setHref($this->uriBuilder->buildUriFromRoute('xima_typo3_manual', ['id' => $pageId]));
        $manualButton->setTitle('Manual');
        $manualButton->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', Icon::SIZE_SMALL));
        $buttons = $event->getButtons();
        $buttons['right'] ??= [];
        $buttons['right'][] = [$manualButton];

        $event->setButtons($buttons);
    }
}
