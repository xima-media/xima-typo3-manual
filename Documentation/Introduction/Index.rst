..  _introduction:

============
Introduction
============

The extension works as a small site package dedicated to documentation. A manual
has its own root page, site configuration and page tree. Pages of type
:guilabel:`Manual page` (doktype ``701``) are chapters. The site set
``xima/manual`` supplies the frontend rendering, page TSconfig and RTE preset.

The :guilabel:`Editor Manual` backend module embeds that frontend in an iframe.
It is not a private backend-only document store: see :ref:`access` before adding
confidential content.

Features
========

*   Chapter navigation, an overview and an "on this page" column.
*   A continuous manual or one chapter per page, configurable per site.
*   Rich text with images, five note styles, steps in four layouts and annotated
    screenshots.
*   Related-record links in backend docheaders and a documentation coverage
    report.
*   Multiple manuals with a selector and remembered selection.
*   Full-text search through TYPO3's indexed search.
*   A backend PDF download and a printable frontend page type.
*   Optional inline editing with EXT:visual_editor.
*   A configurable primary colour, light and dark modes, and locally shipped
    Source Sans 3 / Source Serif 4 fonts.

Read :ref:`editor-guide` for the authoring workflow, :ref:`site-settings` for the
complete settings reference and :ref:`upgrade` when updating from 2.x.
