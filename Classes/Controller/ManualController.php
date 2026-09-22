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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Xima\XimaTypo3Manual\Configuration;

class ManualController extends ActionController
{
    public function __construct(
        protected ModuleTemplateFactory $moduleTemplateFactory,
        protected IconFactory $iconFactory,
        protected PageRenderer $pageRenderer,
        protected PageRepository $pageRepository,
        protected SiteFinder $siteFinder,
        private readonly ConnectionPool $connectionPool,
        private readonly BackendUriBuilder $backendUriBuilder
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
            $pageId = $this->getUidOfFirstAccessibleManualPage();
            if ($pageId === 0) {
                $uri = $this->uriBuilder->uriFor('index', ['context' => $context], 'Installation');
                return new RedirectResponse($uri);
            }
        }

        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/Navigation.js');
        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/EditRecords.js');
        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/Slider.js');
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf');

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setBodyTag('<body class="typo3-module-xima_typo3_manual">');
        $moduleTemplate->setTitle($this->translate('mlang_tabs_tab'));

        $languageId = $this->getCurrentLanguage($pageId, $this->resolveLanguageParameter());
        $targetUrl = (string)PreviewUriBuilder::create($pageId)
            ->withSection('p' . $pageId)
            ->withAdditionalQueryParameters(['context' => $context])
            ->withLanguage($languageId)
            ->buildUri();
        $this->registerDocHeader($moduleTemplate, $pageId, $languageId, $context);

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

    /**
     * The module is reachable without any page argument, so the first manual the user may read is used as entry point.
     */
    protected function getUidOfFirstAccessibleManualPage(): int
    {
        $qb = $this->connectionPool->getQueryBuilderForTable('pages');
        $pages = $qb->select('uid')
            ->from('pages')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq('doktype', $qb->createNamedParameter(Configuration::DOKTYPE_MANUAL, Connection::PARAM_INT)),
                    $qb->expr()->eq('is_siteroot', $qb->createNamedParameter(1, Connection::PARAM_INT)),
                )
            )
            ->orderBy('sorting', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($pages as $row) {
            $access = BackendUtility::readPageAccess(
                (int)$row['uid'],
                $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW)
            );
            if ($access !== false) {
                return (int)$row['uid'];
            }
        }

        return 0;
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

    protected function registerDocHeader(ModuleTemplate $moduleTemplate, int $pageId, int $languageId, string $context): void
    {
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
