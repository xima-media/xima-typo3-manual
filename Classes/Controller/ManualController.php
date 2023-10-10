<?php

namespace Xima\XimaTypo3Manual\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\PreviewUriBuilder;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Routing\UnableToLinkToPageException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ManualController extends ActionController
{
    protected ?ModuleTemplate $moduleTemplate = null;

    public function __construct(protected ModuleTemplateFactory $moduleTemplateFactory, protected IconFactory $iconFactory, protected PageRenderer $pageRenderer, protected PageRepository $pageRepository, protected SiteFinder $siteFinder)
    {
    }

    protected function isManualRootPage(int $pageUid): bool
    {
        $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        return isset($rootline[0], $rootline[0]['doktype']) && $rootline[0]['doktype'] === 701;
    }

    protected function getUidOfFirstManualPage(): int
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $page = $qb->select('uid')
            ->from('pages')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq('doktype', $qb->createNamedParameter(701, \PDO::PARAM_INT)),
                    $qb->expr()->eq('is_siteroot', $qb->createNamedParameter(1, \PDO::PARAM_INT)),
                )
            )
            ->executeQuery()
            ->fetchOne();

        return $page ?: 0;
    }

    public function indexAction(): ResponseInterface
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/ManualModule');

        /* @phpstan-ignore-next-line */
        $this->view->setTemplateRootPaths(['EXT:xima_typo3_manual/Resources/Private/Templates']);
        /* @phpstan-ignore-next-line */
        $this->view->setPartialRootPaths(['EXT:xima_typo3_manual/Resources/Private/Partials']);
        /* @phpstan-ignore-next-line */
        $this->view->setLayoutRootPaths(['EXT:xima_typo3_manual/Resources/Private/Layouts']);

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->getLanguageService()->includeLLFile('EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf');
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf');
        $pageId = (int)($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? 0);

        if (!$this->isManualRootPage($pageId)) {
            $pageId = $this->getUidOfFirstManualPage();
        }

        $this->moduleTemplate->setBodyTag('<body class="typo3-module-xima_typo3_manual">');
        $this->moduleTemplate->setModuleId('typo3-module-manual');

        $pageinfo = BackendUtility::readPageAccess(
            $pageId,
            $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW)
        );

        $this->moduleTemplate->setTitle(
            $this->getLanguageService()->sL('LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang_mod.xlf:mlang_tabs_tab'),
            $pageinfo['title'] ?? ''
        );

        $languageId = $this->getCurrentLanguage(
            $pageId,
            $this->request->getParsedBody()['language'] ?? $this->request->getQueryParams()['language'] ?? null
        );

        try {
            $targetUrl = (string)PreviewUriBuilder::create($pageId)->withSection('')->withAdditionalQueryParameters($this->getTypeParameterIfSet($pageId) . '&L=' . $languageId)->buildUri();
        } catch (UnableToLinkToPageException) {
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->getLanguageService()->getLL('noSiteConfiguration'),
                '',
                ContextualFeedbackSeverity::WARNING
            );
            return $this->renderFlashMessage($flashMessage);
        }

        $languageId = $this->getCurrentLanguage(
            $pageId,
            $this->request->getParsedBody()['language'] ?? $this->request->getQueryParams()['language'] ?? null
        );
        $this->registerDocHeader($pageId, $languageId, $targetUrl, $this->request->getQueryParams()['route']);

        $this->view->assign('url', $targetUrl);
        $this->view->assign('pid', $pageId);

        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Returns the current language
     *
     * @return int
     */
    protected function getCurrentLanguage(int $pageId, string $languageParam = null): int
    {
        $languageId = (int)$languageParam;
        if ($languageParam === null) {
            $states = $this->getBackendUser()->uc['moduleData']['web_view']['States'] ?? [];
            $languages = $this->getPreviewLanguages($pageId);
            if (isset($states['languageSelectorValue']) && isset($languages[$states['languageSelectorValue']])) {
                $languageId = (int)$states['languageSelectorValue'];
            }
        } else {
            $this->getBackendUser()->uc['moduleData']['web_view']['States']['languageSelectorValue'] = $languageId;
            $this->getBackendUser()->writeUC();
        }
        return $languageId;
    }

    /**
     * Returns the preview languages
     *
     * @return array
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
            // do nothing
        }
        return $languages;
    }

    /**
     * With page TS config it is possible to force a specific type id via mod.web_view.type
     * for a page id or a page tree.
     * The method checks if a type is set for the given id and returns the additional GET string.
     *
     * @return string
     */
    protected function getTypeParameterIfSet(int $pageId): string
    {
        $typeParameter = '';
        $typeId = (int)(BackendUtility::getPagesTSconfig($pageId)['mod.']['web_view.']['type'] ?? 0);
        if ($typeId > 0) {
            $typeParameter = '&type=' . $typeId;
        }
        return $typeParameter;
    }

    protected function renderFlashMessage(FlashMessage $flashMessage): HtmlResponse
    {
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);

        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * Register the doc header
     */
    protected function registerDocHeader(int $pageId, int $languageId, string $targetUrl, ?string $route): void
    {
        $languages = $this->getPreviewLanguages($pageId);
        if (count($languages) > 1) {
            $languageMenu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $languageMenu->setIdentifier('_langSelector');
            foreach ($languages as $value => $label) {
                //$href = (string)$this->uriBuilder->buildUriFromRoute(
                //    'web_ViewpageView',
                //    [
                //        'id' => $pageId,
                //        'language' => (int)$value,
                //    ]
                //);
                $href = '';
                $menuItem = $languageMenu->makeMenuItem()
                    ->setTitle($label)
                    ->setHref($href);
                if ($languageId === (int)$value) {
                    $menuItem->setActive(true);
                }
                $languageMenu->addMenuItem($menuItem);
            }
            $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($languageMenu);
        }

        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $showButton = $buttonBar->makeLinkButton()
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
            ->setIcon($this->iconFactory->getIcon('actions-view-page', Icon::SIZE_SMALL));
        $buttonBar->addButton($showButton);

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $downloadUrl = $uriBuilder->buildUriFromRoute('manual-download-pdf', ['id' => $pageId]);
        $showButton = $buttonBar->makeLinkButton()
            ->setHref($downloadUrl)
            ->setClasses('xima-typo3-manual-download-pdf')
            ->setTitle('Download')
            ->setIcon($this->iconFactory->getIcon('actions-download', Icon::SIZE_SMALL));
        $buttonBar->addButton($showButton);

        $refreshButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setClasses('t3js-viewpage-refresh')
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:viewpage/Resources/Private/Language/locallang.xlf:refreshPage'))
            ->setIcon($this->iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
        $buttonBar->addButton($refreshButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
    }
}
