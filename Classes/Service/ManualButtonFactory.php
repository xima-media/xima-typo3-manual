<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Service;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownDivider;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownHeader;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownItem;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDownButton;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Controller\ManualController;

/**
 * Builds the manual button shown in every backend doc header: a dropdown of matching chapters, a preview button
 * while editing the manual itself, or a plain link to the whole manual.
 */
final readonly class ManualButtonFactory
{
    public function __construct(
        private IconFactory $iconFactory,
        private UriBuilder $uriBuilder,
        private ManualRegistry $manualRegistry
    ) {
    }

    /**
     * @param array<int, list<array<string, mixed>>> $manualPages Chapters grouped by the manual they belong to
     */
    public function dropdown(array $manualPages): DropDownButton
    {
        $dropdown = GeneralUtility::makeInstance(DropDownButton::class);
        $dropdown->setLabel($this->translate('button.dropdown'));
        $dropdown->setTitle($this->translate('button.dropdown.title'));
        $dropdown->setShowLabelText(true);
        $dropdown->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', IconSize::SMALL));

        $showManualTitles = count($manualPages) > 1;

        if (!$showManualTitles) {
            $dropdown->addItem(
                GeneralUtility::makeInstance(DropDownHeader::class)
                    ->setLabel($this->translate('button.dropdown.header'))
            );
        }

        $key = 0;
        foreach ($manualPages as $manualRoot => $chapters) {
            if ($showManualTitles) {
                $manualTitle = $this->manualRegistry->getTitle($manualRoot);
                $dropdown->addItem(
                    GeneralUtility::makeInstance(DropDownHeader::class)
                        ->setLabel($manualTitle !== '' ? $manualTitle : $this->translate('button.dropdown.header'))
                );
            }

            foreach ($chapters as $manualPage) {
                $key++;
                $item = GeneralUtility::makeInstance(DropDownItem::class);
                $title = (string)($manualPage['title'] ?? $manualPage['header'] ?? '');
                $title = $title !== '' ? $title : $this->translate('button.dropdown.no-title') . ' ' . $key;
                $pid = (int)($manualPage['pid'] ?? $manualPage['uid'] ?? 0);

                $item->setIcon($this->iconFactory->getIcon('actions-dot', IconSize::SMALL));
                $item->setLabel($title);
                $item->setAttributes([
                    'data-manual-modal' => 'open',
                    'data-manual-backend-url' => $this->manualUri($pid, 'backend'),
                ]);
                $item->setHref($this->manualUri($pid, 'iframe'));
                $dropdown->addItem($item);
            }
        }

        $dropdown->addItem(GeneralUtility::makeInstance(DropDownDivider::class));

        $allChapters = GeneralUtility::makeInstance(DropDownItem::class);
        $allChapters->setHref($this->manualUri(0, 'iframe'));
        $allChapters->setTitle($this->translate('button.dropdown.all.title'));
        $allChapters->setLabel($this->translate('button.dropdown.all'));
        $allChapters->setAttributes([
            'data-manual-modal' => 'open',
            'data-manual-backend-url' => $this->manualUri(0, 'backend'),
        ]);
        $allChapters->setIcon($this->iconFactory->getIcon('actions-notebook', IconSize::SMALL));
        $dropdown->addItem($allChapters);

        return $dropdown;
    }

    public function preview(int $pageId): LinkButton
    {
        $button = GeneralUtility::makeInstance(LinkButton::class);

        return $button
            ->setHref($this->manualUri($pageId, 'backend'))
            ->setTitle($this->translate('button.preview'))
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', IconSize::SMALL))
            ->setDataAttributes(['manual-preview' => ManualController::getRootPageUid($pageId)]);
    }

    public function small(int $pageId): LinkButton
    {
        $button = GeneralUtility::makeInstance(LinkButton::class);

        return $button
            ->setHref($this->manualUri($pageId, 'iframe'))
            ->setTitle($this->translate('button.dropdown.all.title'))
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('apps-pagetree-manual-root', IconSize::SMALL))
            ->setDataAttributes([
                'manual-modal' => 'open',
                'manual-backend-url' => $this->manualUri($pageId, 'backend'),
            ]);
    }

    private function manualUri(int $pageId, string $context): string
    {
        return (string)$this->uriBuilder->buildUriFromRoute(
            Configuration::EXTENSION_KEY,
            [
                'id' => $pageId,
                'context' => $context,
                'language' => $this->getBackendUser()->uc['lang'] ?? '',
            ]
        );
    }

    private function translate(string $key): string
    {
        return $this->getLanguageService()?->sL(Configuration::LANGUAGE_FILE . $key) ?? $key;
    }

    private function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
