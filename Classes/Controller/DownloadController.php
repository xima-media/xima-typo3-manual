<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\PreviewUriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XimaTypo3Manual\Configuration;

/**
 * Renders a manual as PDF by fetching its printable page type and passing the HTML through dompdf.
 */
final class DownloadController
{
    public function downloadPdf(ServerRequestInterface $request): ResponseInterface
    {
        $pageId = (int)($request->getQueryParams()['id'] ?? 0);
        $languageId = (int)($request->getQueryParams()['language'] ?? 0);

        $pageRecord = $pageId > 0
            ? BackendUtility::readPageAccess($pageId, $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW))
            : false;

        if (!is_array($pageRecord) || !ManualController::hasManualRootPage($pageId)) {
            return new Response('php://temp', 403, ['Content-Type' => 'text/plain'], 'No manual accessible for this page');
        }

        $targetUrl = (string)PreviewUriBuilder::create($pageId)
            ->withSection('')
            ->withAdditionalQueryParameters(['type' => Configuration::PDF_PAGE_TYPE])
            ->withLanguage($languageId)
            ->buildUri();

        $html = GeneralUtility::getUrl($targetUrl);
        if (!is_string($html) || $html === '') {
            return new Response('php://temp', 502, ['Content-Type' => 'text/plain'], 'Could not render the manual');
        }

        $options = new Options();
        // Everything the PDF needs is inlined by EncodeImagesBase64Middleware, so dompdf never has to fetch a URL
        $options->setIsRemoteEnabled(false);
        $options->setIsHtml5ParserEnabled(true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $response = new Response('php://temp', 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $this->getFilename($pageRecord) . '"',
        ]);
        $response->getBody()->write((string)$dompdf->output());

        return $response;
    }

    /**
     * @param array<string, mixed> $pageRecord
     */
    private function getFilename(array $pageRecord): string
    {
        $title = preg_replace('/[^\w\-]+/u', '-', (string)($pageRecord['title'] ?? 'manual')) ?: 'manual';

        return trim($title, '-') . '.pdf';
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
