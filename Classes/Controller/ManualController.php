<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\PreviewUriBuilder;
use TYPO3\CMS\Backend\Routing\UriBuilder as BackendUriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Backend\Template\Components\Menu\Menu;
use TYPO3\CMS\Backend\Template\Components\Menu\MenuItem;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Service\ManualRegistry;

class ManualController extends ActionController
{
    public function __construct(
        protected ModuleTemplateFactory $moduleTemplateFactory,
        protected IconFactory $iconFactory,
        protected PageRenderer $pageRenderer,
        protected PageRepository $pageRepository,
        protected SiteFinder $siteFinder,
        private readonly BackendUriBuilder $backendUriBuilder,
        private readonly ManualRegistry $manualRegistry
    ) {
    }

    public static function getRootPageUid(int $pageUid): int
    {
        if ($pageUid <= 0) {
            return 0;
        }

        $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        return (int)($rootline[0]['uid'] ?? 0);
    }

    public function indexAction(): ResponseInterface
    {
        $context = $this->resolveContext();
        $pageId = $this->resolveRequestedPageId();
        if (!self::hasManualRootPage($pageId)) {
            $pageId = $this->manualRegistry->getPreferredManualRoot();
            if ($pageId === 0) {
                $uri = $this->uriBuilder->uriFor('index', ['context' => $context], 'Installation');
                return new RedirectResponse($uri);
            }
        }

        $manualRoot = $this->manualRegistry->getManualRootForPage($pageId);
        $this->manualRegistry->rememberManualRoot($manualRoot);

        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/Navigation.js');
        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/EditRecords.js');
        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/Slider.js');
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf');

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setBodyTag('<body class="typo3-module-xima_typo3_manual">');
        $moduleTemplate->setTitle(
            $this->translate('mlang_tabs_tab'),
            $this->manualRegistry->getTitle($manualRoot)
        );

        $languageId = $this->getCurrentLanguage($pageId, $this->resolveLanguageParameter());
        $targetUrl = (string)PreviewUriBuilder::create($pageId)
            ->withSection('p' . $pageId)
            ->withAdditionalQueryParameters(['context' => $context])
            ->withLanguage($languageId)
            ->buildUri();
        $this->registerDocHeader($moduleTemplate, $pageId, $manualRoot, $languageId, $context);

        if ($context === 'iframe') {
            $moduleTemplate->getDocHeaderComponent()->disable();
        }

        $moduleTemplate->assign('url', $targetUrl);
        $moduleTemplate->assign('pid', $pageId);
        $moduleTemplate->assign('context', $context);

        return $moduleTemplate->renderResponse('Manual/Index');
    }

    public static function hasManualRootPage(int $pageUid): bool
    {
        if ($pageUid <= 0) {
            return false;
        }

        $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        return (int)($rootline[0]['doktype'] ?? 0) === Configuration::DOKTYPE_MANUAL;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    protected function getCurrentLanguage(int $pageId, ?string $languageParam = null): int
    {
        $languageId = (int)$languageParam;
        if ($languageParam === null) {
            $states = $this->getBackendUser()->uc['moduleData']['web_view']['States'] ?? [];
            $languages = $this->getPreviewLanguages($pageId);
            if (isset($states['languageSelectorValue'], $languages[$states['languageSelectorValue']])) {
                $languageId = (int)$states['languageSelectorValue'];
            }
        } else {
            $this->getBackendUser()->uc['moduleData']['web_view']['States']['languageSelectorValue'] = $languageId;
            $this->getBackendUser()->writeUC();
        }

        return $languageId;
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return array<int, string>
     */
    protected function getPreviewLanguages(int $pageId): array
    {
        $languages = [];
        $modSharedTSconfig = BackendUtility::getPagesTSconfig($pageId)['mod.']['SHARED.'] ?? [];
        if (($modSharedTSconfig['view.']['disableLanguageSelector'] ?? false) === '1') {
            return $languages;
        }

        try {
            $site = $this->siteFinder->getSiteByPageId($pageId);
            $siteLanguages = $site->getAvailableLanguages($this->getBackendUser(), false, $pageId);

            foreach ($siteLanguages as $siteLanguage) {
                $languageAspectToTest = LanguageAspectFactory::createFromSiteLanguage($siteLanguage);
                $page = $this->pageRepository->getPageOverlay(
                    $this->pageRepository->getPage($pageId),
                    $siteLanguage->getLanguageId()
                );

                if ($this->pageRepository->isPageSuitableForLanguage($page, $languageAspectToTest)) {
                    $languages[$siteLanguage->getLanguageId()] = $siteLanguage->getTitle();
                }
            }
        } catch (SiteNotFoundException) {
            // Manuals without a site configuration simply offer no language selector
        }

        return $languages;
    }

    protected function registerDocHeader(ModuleTemplate $moduleTemplate, int $pageId, int $manualRoot, int $languageId, string $context): void
    {
        $this->registerManualMenu($moduleTemplate, $manualRoot, $context);

        $languages = $this->getPreviewLanguages($pageId);
        if (count($languages) > 1) {
            $languageMenu = GeneralUtility::makeInstance(Menu::class);
            $languageMenu->setIdentifier('_langSelector');
            foreach ($languages as $value => $label) {
                $href = $this->uriBuilder->uriFor(
                    'index',
                    [
                        'id' => $pageId,
                        'language' => (int)$value,
                    ]
                );
                $menuItem = GeneralUtility::makeInstance(MenuItem::class);
                $menuItem
                    ->setTitle($label)
                    ->setHref($href);
                if ($languageId === (int)$value) {
                    $menuItem->setActive(true);
                }

                $languageMenu->addMenuItem($menuItem);
            }

            $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($languageMenu);
        }

        $targetUrl = (string)PreviewUriBuilder::create($pageId)->withSection('')->withLanguage($languageId)->buildUri();
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        if ($targetUrl !== '') {
            $showButton = GeneralUtility::makeInstance(LinkButton::class);
            $showButton
                ->setHref($targetUrl)
                ->setDataAttributes([
                    'dispatch-action' => 'TYPO3.WindowManager.localOpen',
                    'dispatch-args' => GeneralUtility::jsonEncodeForHtmlAttribute([
                        $targetUrl,
                        true, // switchFocus
                        'newTYPO3frontendWindow', // windowName,
                    ]),
                ])
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.showPage'))
                ->setShowLabelText(true)
                ->setIcon($this->iconFactory->getIcon('actions-view-page', IconSize::SMALL));
            $buttonBar->addButton($showButton);
        }

        $downloadUrl = $this->backendUriBuilder->buildUriFromRoute(
            'manual-download-pdf',
            ['id' => $pageId, 'language' => $languageId]
        );
        $downloadButton = GeneralUtility::makeInstance(LinkButton::class);
        $downloadButton
            ->setHref((string)$downloadUrl)
            ->setClasses('xima-typo3-manual-download-pdf')
            ->setTitle($this->translate('button.download.pdf'))
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('actions-download', IconSize::SMALL));
        $buttonBar->addButton($downloadButton);

        if ($context === 'backend') {
            $coverageButton = GeneralUtility::makeInstance(LinkButton::class);
            $coverageButton
                ->setHref($this->uriBuilder->uriFor('index', ['id' => $pageId], 'Coverage'))
                ->setTitle($this->translate('button.coverage'))
                ->setShowLabelText(true)
                ->setIcon($this->iconFactory->getIcon('actions-list-alternative', IconSize::SMALL));
            $buttonBar->addButton($coverageButton);

            $returnUid = $this->resolveRequestedPageId();
            if ($returnUid !== $pageId) {
                $label = 'button.manual.close';
                $class = 'xima-typo3-manual-close';
            } else {
                $label = 'button.preview.close';
                $class = 'xima-typo3-manual-preview-stop';
            }
            $closePreviewButton = GeneralUtility::makeInstance(LinkButton::class);
            $closePreviewButton
                ->setHref((string)$this->backendUriBuilder->buildUriFromRoute('web_layout', ['id' => $returnUid]))
                ->setClasses($class)
                ->setTitle($this->translate($label))
                ->setShowLabelText(true)
                ->setIcon($this->iconFactory->getIcon('actions-close', IconSize::SMALL));
            $buttonBar->addButton($closePreviewButton, ButtonBar::BUTTON_POSITION_RIGHT, 2);
        }
    }

    /**
     * With more than one manual in the installation the doc header offers a menu to switch between them.
     */
    protected function registerManualMenu(ModuleTemplate $moduleTemplate, int $manualRoot, string $context): void
    {
        if ($context !== 'backend' || !$this->manualRegistry->hasSeveralManuals()) {
            return;
        }

        $menu = GeneralUtility::makeInstance(Menu::class);
        $menu->setIdentifier('_manualSelector');
        $menu->setLabel($this->translate('menu.manual'));

        foreach ($this->manualRegistry->getAccessibleManuals() as $manual) {
            $menuItem = GeneralUtility::makeInstance(MenuItem::class);
            $menuItem
                ->setTitle($manual->title !== '' ? $manual->title : '#' . $manual->uid)
                ->setHref($this->uriBuilder->uriFor('index', ['id' => $manual->uid]));
            if ($manual->uid === $manualRoot) {
                $menuItem->setActive(true);
            }
            $menu->addMenuItem($menuItem);
        }

        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    protected function resolveContext(): string
    {
        $context = (string)($this->request->getQueryParams()['context'] ?? 'backend');
        return in_array($context, ['backend', 'iframe'], true) ? $context : 'backend';
    }

    protected function resolveRequestedPageId(): int
    {
        $parsedBody = $this->request->getParsedBody();
        $id = (is_array($parsedBody) ? $parsedBody['id'] ?? null : null)
            ?? $this->request->getQueryParams()['id']
            ?? 0;

        return max(0, (int)$id);
    }

    protected function resolveLanguageParameter(): ?string
    {
        $parsedBody = $this->request->getParsedBody();
        $language = (is_array($parsedBody) ? $parsedBody['language'] ?? null : null)
            ?? $this->request->getQueryParams()['language']
            ?? null;

        return $language === null ? null : (string)$language;
    }

    protected function translate(string $key): string
    {
        return $this->getLanguageService()->sL(Configuration::LANGUAGE_FILE . $key);
    }
}
