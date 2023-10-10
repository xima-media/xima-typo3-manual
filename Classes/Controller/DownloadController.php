<?php

namespace Xima\XimaTypo3Manual\Controller;

use Dompdf\Dompdf;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\PreviewUriBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;

class DownloadController
{
    public function downloadPdf(ServerRequest $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $pageId = $params['id'];

        $targetUrl = (string)PreviewUriBuilder::create($pageId)->withSection('')->withAdditionalQueryParameters('&type=1664618986')->buildUri();

        $html = file_get_contents($targetUrl);

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
