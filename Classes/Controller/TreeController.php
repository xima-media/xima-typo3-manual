<?php

namespace Xima\XimaTypo3Manual\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Tree\Repository\PageTreeRepository;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XimaTypo3Manual\Database\Query\Restriction\DocumentTypeExclusiveRestriction;

class TreeController extends \TYPO3\CMS\Backend\Controller\Page\TreeController
{
    protected ?ServerRequestInterface $request = null;

    public function fetchDataAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        return parent::fetchDataAction($request);
    }

    protected function getPageTreeRepository(): PageTreeRepository
    {
        if (!$this->request) {
            /* @ToDo */
            /* @phpstan-ignore-next-line */
            return parent::getPageTreeRepository();
        }

        /** @var NormalizedParams $params */
        $params = $this->request->getAttribute('normalizedParams');
        $referer = $params->getHttpReferer();

        if (str_contains($referer, 'help/XimaTypo3Manual')) {
            $this->hiddenRecords = [];
            return $this->getPageTreeRepositoryForManualModule();
        }

        /* @ToDo */
        /* @phpstan-ignore-next-line */
        return parent::getPageTreeRepository();
    }

    protected function getPageTreeRepositoryForManualModule(): PageTreeRepository
    {
        $additionalQueryRestrictions = [];
        $backendUser = $this->getBackendUser();
        $exclusiveDocumentTypes = [701];

        $additionalQueryRestrictions[] = GeneralUtility::makeInstance(
            DocumentTypeExclusiveRestriction::class,
            $exclusiveDocumentTypes
        );

        return GeneralUtility::makeInstance(
            PageTreeRepository::class,
            (int)$backendUser->workspace,
            [],
            $additionalQueryRestrictions
        );
    }
}
