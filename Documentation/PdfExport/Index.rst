..  _pdf-export:

==========
PDF export
==========

Use :guilabel:`Download PDF` in the manual module's docheader. The backend route
``manual-download-pdf`` renders the printable frontend HTML with dompdf and
returns an A4 PDF. Its filename is derived from the requested page's title, with
unsafe filename characters replaced.

The printable PAGE has ``typeNum = 1664618986``. It always combines all chapters,
even if the web view displays one chapter per page. Interactive steps are
flattened to a list. The export uses print-specific styling; it is not a pixel
copy of the browser view.

Access rules
============

*   The download route requires a backend login.
*   The requested ``id`` must be readable by the backend user and belong to a
    manual root. Invalid or inaccessible targets return HTTP 403.
*   The ``language`` parameter selects the preview language.
*   The printable **frontend** URL is separate and is not protected by those
    backend checks. Anyone able to access the manual site can also request
    ``?type=1664618986`` unless the site is protected separately.

Never publish a session-token download URL as a documentation link. Use the
backend's download button, which generates the current user's route URL.

Rendering and assets
====================

The controller fetches the printable page server-side. That URL must resolve and
be reachable from the PHP environment. A failed/empty fetch returns HTTP 502.
Reverse-proxy restrictions and HTTP authentication also apply to this fetch.

The printable middleware inlines local images and styles; image files must be
inside TYPO3's public path and must be recognized as images. Remote resource
loading in dompdf is disabled. Avoid externally hosted images and verify custom
CSS in the actual PDF, since dompdf does not support all modern browser CSS.

For confidential manuals protect the frontend, printable route and public image
files together; see :ref:`access`.
