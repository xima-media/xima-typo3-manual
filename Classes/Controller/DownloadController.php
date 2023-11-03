<?php

namespace Xima\XimaTypo3Manual\Controller;

use Dompdf\Dompdf;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\PreviewUriBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DownloadController
{
    public function downloadPdf(ServerRequest $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $pageId = $params['id'];
        $languageId = $params['language'] ?? 0;

        $targetUrl = (string)PreviewUriBuilder::create($pageId)->withSection('')->withAdditionalQueryParameters('&type=1664618986')->withLanguage($languageId)->buildUri();

        $html = GeneralUtility::getUrl($targetUrl) ?: '';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return new Response(
            200,
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="Handbuch.pdf"'],
            $dompdf->output()
        );
    }
}
