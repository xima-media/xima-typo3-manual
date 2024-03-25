<?php

namespace Xima\XimaTypo3Manual\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class InstallationController extends ActionController
{
    public function indexAction(): ResponseInterface
    {
        return $this->htmlResponse('welcome to the installation');
    }
}
