<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Service\CoverageService;
use Xima\XimaTypo3Manual\Service\ManualRegistry;

/**
 * Lists every documentable record type next to the chapters documenting it, so gaps in the manual become visible.
 */
class CoverageController extends ActionController
{
    use ManualMenuTrait;

    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly CoverageService $coverageService,
        private readonly IconFactory $iconFactory,
        private readonly PageRenderer $pageRenderer,
        private readonly ManualRegistry $manualRegistry
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $pageId = (int)($this->request->getQueryParams()['id'] ?? 0);
        $rootPageUid = $this->manualRegistry->getManualRootForPage($pageId)
            ?: $this->manualRegistry->getPreferredManualRoot();

        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/Coverage.js');

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setTitle($this->translate('coverage.title'), $this->manualRegistry->getTitle($rootPageUid));
        $this->registerManualMenu($moduleTemplate, $rootPageUid);

        $backButton = GeneralUtility::makeInstance(LinkButton::class);
        $backButton
            ->setHref($this->uriBuilder->uriFor('index', ['id' => $pageId], 'Manual'))
            ->setTitle($this->translate('button.manual.close'))
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('actions-arrow-left-alt', IconSize::SMALL));
        $moduleTemplate->getDocHeaderComponent()->getButtonBar()->addButton($backButton);

        $moduleTemplate->assignMultiple($this->coverageService->getCoverage($rootPageUid));
        $moduleTemplate->assign('manualTitle', $this->manualRegistry->getTitle($rootPageUid));

        return $moduleTemplate->renderResponse('Coverage/Index');
    }

    private function translate(string $key): string
    {
        return $this->getLanguageService()->sL(Configuration::LANGUAGE_FILE . $key);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
