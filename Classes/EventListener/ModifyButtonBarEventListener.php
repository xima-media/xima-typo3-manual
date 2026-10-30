<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\EventListener;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Page\PageRenderer;
use Xima\XimaTypo3Manual\Controller\ManualController;
use Xima\XimaTypo3Manual\Service\ManualButtonFactory;
use Xima\XimaTypo3Manual\Service\ManualRegistry;
use Xima\XimaTypo3Manual\Service\ManualRelationLookupService;
use Xima\XimaTypo3Manual\Utility\RequestParameterResolver;

/**
 * Adds the manual button to every backend doc header. Which button is shown depends on what the current view is
 * documented by: a dropdown of matching chapters, a preview button while editing the manual itself, or a plain link
 * to the whole manual.
 */
final readonly class ModifyButtonBarEventListener
{
    public function __construct(
        private PageRenderer $pageRenderer,
        private ManualRegistry $manualRegistry,
        private ManualRelationLookupService $relationLookup,
        private ManualButtonFactory $buttonFactory
    ) {
    }

    #[AsEventListener(identifier: 'xima-typo3-manual/modify-button-bar')]
    public function __invoke(ModifyButtonBarEvent $event): void
    {
        $request = $this->getRequest($event);
        if (!$request instanceof ServerRequestInterface) {
            return;
        }

        $pageId = RequestParameterResolver::getPageId($request);

        $path = $request->getUri()->getPath();
        $isRecordEdit = str_contains($path, 'record/edit');
        if (str_contains($path, 'help/manual') || ($pageId === 0 && !$isRecordEdit)) {
            return;
        }

        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/ManualModal.js');
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf');

        $manualPages = $this->groupByManual($this->relationLookup->findForRecord($request, $pageId, $isRecordEdit));

        if ($manualPages !== []) {
            $button = $this->buttonFactory->dropdown($manualPages);
        } elseif (ManualController::hasManualRootPage($pageId)) {
            $button = $this->buttonFactory->preview($pageId);
        } else {
            $button = $this->buttonFactory->small($pageId);
        }

        $buttons = $event->getButtons();
        $buttons['right'] ??= [];
        $buttons['right'][] = [$button];
        $event->setButtons($buttons);
    }

    /**
     * ModifyButtonBarEvent only carries the request since TYPO3 v14.
     */
    private function getRequest(ModifyButtonBarEvent $event): ?ServerRequestInterface
    {
        if (method_exists($event, 'getRequest')) {
            return $event->getRequest();
        }

        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;

        return $request instanceof ServerRequestInterface ? $request : null;
    }

    /**
     * Chapters of manuals the user may not read are dropped, the rest is grouped by the manual they belong to.
     *
     * @param list<array<string, mixed>> $manualPages
     * @return array<int, list<array<string, mixed>>>
     */
    private function groupByManual(array $manualPages): array
    {
        $grouped = [];
        foreach ($manualPages as $manualPage) {
            $pageUid = (int)($manualPage['pid'] ?? $manualPage['uid'] ?? 0);
            $manualRoot = $this->manualRegistry->getManualRootForPage($pageUid);
            if ($manualRoot === 0 || !$this->manualRegistry->isAccessible($manualRoot)) {
                continue;
            }
            $grouped[$manualRoot][] = $manualPage;
        }

        return $grouped;
    }
}
