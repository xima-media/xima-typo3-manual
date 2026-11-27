..  _known-problems:

==============
Known problems
==============

Frontend exposure
=================

The frontend and printable page type are not backend-authenticated. Protect
private manuals at the web server and include image files and search URLs.
See :ref:`access`.

Search is not immediate
=======================

Backend previews do not index the manual. Search requires a rendered frontend
page and respects indexed_search's reindexing interval. See :ref:`search`.

Visual-editor insertion target
==============================

In full-manual mode, structure changes use the requested chapter, not whichever
chapter is visible further down the page. Open the target chapter first. See
:ref:`visual-editor`.

PDF differences and unreachable URLs
===================================

dompdf is not a browser and may render custom CSS differently. The PHP server
must be able to fetch the printable frontend URL; a proxy or an inaccessible
site base can make the download fail with HTTP 502. External assets are not
fetched by dompdf. See :ref:`pdf-export`.

Classic / TER installations
===========================

The extension depends on ``dompdf/dompdf`` through Composer. The repository's
release workflow does not bundle Composer's vendor directory. A standalone TER
ZIP therefore is not sufficient for the PDF feature in classic mode: provide a
working dompdf autoload separately or use a Composer installation. Do not assume
that installing only the TYPO3 extension dependencies also installs this library.

Reporting problems
==================

Use the `GitHub issue tracker
<https://github.com/xima-media/xima-typo3-manual/issues>`__. Include TYPO3, PHP
and extension versions, reproduction steps and whether the issue occurs in the
normal frontend, backend iframe or printable view. Keep credentials, route
tokens and private manual content out of reports and screenshots.
