<?php

namespace Xima\XimaTypo3Manual\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Exception\AccessDeniedException;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\StandaloneView;
use Xima\XimaTypo3Manual\Generator\ManualGenerator;

class InstallationController extends ActionController
{
    protected ?ModuleTemplate $moduleTemplate = null;

    public function __construct(
        private ModuleTemplateFactory $moduleTemplateFactory,
        private PageRenderer $pageRenderer
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/Installation.js');
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $context = $this->request->getArgument('context') ?? 'backend';
        if ($context === 'iframe') {
            $moduleTemplate->getDocHeaderComponent()->disable();
        }

        $isAdmin = $this->getBackendUser()->isAdmin();
        if (!$isAdmin) {
            return $moduleTemplate->renderResponse('NoAccess');
        }

        return $moduleTemplate->renderResponse();
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
    * @throws AccessDeniedException
    */
    public function installPreset(ServerRequest $request): ResponseInterface
    {
        if (!$this->getBackendUser()->isAdmin()) {
            throw new AccessDeniedException('Only admin users are allowed to create a manual', 1711376718);
        }
        $presetIdentifier = $request->getParsedBody()['preset'] ?? 0;
        $generator = GeneralUtility::makeInstance(ManualGenerator::class);
        $result = $generator->createManualFromPreset($presetIdentifier);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:xima_typo3_manual/Resources/Private/Templates/Installation/Result.html');
        $view->assign('result', $result);

        return new HtmlResponse($view->render());
    }
}
