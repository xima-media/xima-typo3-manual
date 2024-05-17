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
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ManualController extends ActionController
{
    protected ?ModuleTemplate $moduleTemplate = null;

    public function __construct(
        protected ModuleTemplateFactory $moduleTemplateFactory,
        protected IconFactory $iconFactory,
        protected PageRenderer $pageRenderer,
        protected PageRepository $pageRepository,
        protected SiteFinder $siteFinder
    ) {
    }

    public static function getRootPageUid(int $pageUid): int
    {
        $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        return $rootline[0]['uid'] ?? 0;
    }

    public function indexAction(): ResponseInterface
    {
        $context = $this->request->getQueryParams()['context'] ?? 'backend';
        $pageId = (int)($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? 0);
        if (!self::hasManualRootPage($pageId)) {
            $pageId = $this->getUidOfFirstAccessibleManualPage();
            if (!$pageId) {
                $uri = $this->uriBuilder->uriFor('index', ['context' => $context], 'Installation');
                return new RedirectResponse($uri);
            }
        }

        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/Navigation.js');
        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/EditRecords.js');
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf');

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleTemplate->setBodyTag('<body class="typo3-module-xima_typo3_manual">');
        $this->moduleTemplate->setTitle(
            $this->getLanguageService()->sL('LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:mlang_tabs_tab')
        );

        $this->getLanguageService()->includeLLFile('EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf');

        $languageId = $this->getCurrentLanguage(
            $pageId,
            $this->request->getParsedBody()['language'] ?? $this->request->getQueryParams()['language'] ?? null
        );
        $targetUrl = (string)PreviewUriBuilder::create($pageId)->withSection('p' . $pageId)->withAdditionalQueryParameters(['context' => $context])->withLanguage($languageId)->buildUri();
        $this->registerDocHeader($pageId, $languageId, $context);

        if ($context === 'iframe') {
            $this->moduleTemplate->getDocHeaderComponent()->disable();
        }

        $this->moduleTemplate->assign('url', $targetUrl);
        $this->moduleTemplate->assign('pid', $pageId);
        $this->moduleTemplate->assign('context', $context);

        return $this->moduleTemplate->renderResponse();
    }

    public static function hasManualRootPage(int $pageUid): bool
    {
        $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        return isset($rootline[0]['doktype']) && $rootline[0]['doktype'] === 701;
    }

    protected function getUidOfFirstAccessibleManualPage(): int
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $pages = $qb->select('uid')
            ->from('pages')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq('doktype', $qb->createNamedParameter(701, \PDO::PARAM_INT)),
                    $qb->expr()->eq('is_siteroot', $qb->createNamedParameter(1, \PDO::PARAM_INT)),
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($pages as $row) {
            $access = BackendUtility::readPageAccess($row['uid'], $GLOBALS['BE_USER']->getPagePermsClause(Permission::PAGE_SHOW));
            if ($access !== false) {
                return $row['uid'];
            }
        }

        return 0;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    protected function getCurrentLanguage(int $pageId, string $languageParam = null): int
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

    protected function registerDocHeader(int $pageId, int $languageId, string $context): void
    {
        $languages = $this->getPreviewLanguages($pageId);
        if (count($languages) > 1) {
            $languageMenu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $languageMenu->setIdentifier('_langSelector');
            foreach ($languages as $value => $label) {
                $href = $this->uriBuilder->uriFor(
                    'index',
                    [
                        'id' => $pageId,
                        'language' => (int)$value,
                    ]
                );
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

        $targetUrl = (string)PreviewUriBuilder::create($pageId)->withSection('')->withLanguage($languageId)->buildUri();
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
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('actions-view-page', Icon::SIZE_SMALL));
        $buttonBar->addButton($showButton);

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $downloadUrl = $uriBuilder->buildUriFromRoute(
            'manual-download-pdf',
            ['id' => $pageId, 'language' => $languageId]
        );
        $showButton = $buttonBar->makeLinkButton()
            ->setHref($downloadUrl)
            ->setClasses('xima-typo3-manual-download-pdf')
            ->setTitle('Download PDF')
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('actions-download', Icon::SIZE_SMALL));
        $buttonBar->addButton($showButton);

        if ($context === 'backend') {
            $returnUid = (int)($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? 0);
            if ($returnUid !== $pageId) {
                $label = 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:button.manual.close';
                $class = 'xima-typo3-manual-close';
            } else {
                $label = 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:button.preview.close';
                $class = 'xima-typo3-manual-preview-stop';
                $returnUid = $pageId;
            }
            $closePreviewButton = $buttonBar->makeLinkButton()
                ->setHref($uriBuilder->buildUriFromRoute('web_layout', ['id' => $returnUid]))
                ->setClasses($class)
                ->setTitle($this->getLanguageService()->sL($label))
                ->setShowLabelText(true)
                ->setIcon($this->iconFactory->getIcon('actions-close', Icon::SIZE_SMALL));
            $buttonBar->addButton($closePreviewButton, ButtonBar::BUTTON_POSITION_RIGHT, 2);
        }
    }
}
