<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Exception\AccessDeniedException;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Xima\XimaTypo3Manual\Generator\ManualGenerator;

class InstallationController extends ActionController
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly PageRenderer $pageRenderer,
        private readonly ManualGenerator $manualGenerator,
        private readonly ViewFactoryInterface $viewFactory
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $this->pageRenderer->loadJavaScriptModule('@xima/xima-typo3-manual/Installation.js');
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $context = (string)($this->request->getQueryParams()['context'] ?? 'backend');
        if ($context === 'iframe') {
            $moduleTemplate->getDocHeaderComponent()->disable();
        }

        if (!$this->getBackendUser()->isAdmin()) {
            return $moduleTemplate->renderResponse('Installation/NoAccess');
        }

        $moduleTemplate->assign('presets', $this->manualGenerator->getAvailablePresets());

        return $moduleTemplate->renderResponse('Installation/Index');
    }

    /**
     * @throws AccessDeniedException
     */
    public function installPreset(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->getBackendUser()->isAdmin()) {
            throw new AccessDeniedException('Only admin users are allowed to create a manual', 1711376718);
        }

        $parsedBody = $request->getParsedBody();
        $presetIdentifier = (string)(is_array($parsedBody) ? $parsedBody['preset'] ?? '' : '');
        $result = $this->manualGenerator->createManualFromPreset($presetIdentifier, $request);

        $view = $this->viewFactory->create(new ViewFactoryData(
            templateRootPaths: ['EXT:xima_typo3_manual/Resources/Private/Templates/'],
            request: $request,
        ));
        $view->assign('result', $result);

        return new HtmlResponse($view->render('Installation/Result'));
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
