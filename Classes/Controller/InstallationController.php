<?php

namespace Xima\XimaTypo3Manual\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class InstallationController extends ActionController
{
    protected ?ModuleTemplate $moduleTemplate = null;

    public function __construct(
        protected ModuleTemplateFactory $moduleTemplateFactory
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $isAdmin = $this->getBackendUser()->isAdmin();
        $context = $this->request->getArgument('context') ?? 'backend';
        if ($context === 'iframe') {
            $this->moduleTemplate->getDocHeaderComponent()->disable();
        }
        if (!$isAdmin) {
            return $this->moduleTemplate->renderResponse('NoAccess');
        }

        return $this->moduleTemplate->renderResponse();
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
