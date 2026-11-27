..  _customization:

======================
Customization & tokens
======================

Title, logo and lead colour
==========================

*   The manual root page's **title** is the visible title. Its **subtitle** is
    the overview's lead text. ``websiteTitle`` is still useful site metadata,
    but it is not the variable used for the manual's hero title.
*   The first image in the root page's **media** field is the masthead logo.
    If there is none, the template falls back to the backend's ``loginLogo``
    configuration, if present.
*   The **primary colour** is the site setting
    ``manual.appearance.primaryColor``, not the backend login highlight colour.
*   The **eyebrow** above the title is ``manual.appearance.eyebrow``.

Design tokens
=============

The custom properties at the start of
:file:`Resources/Public/Css/Frontend/manual.css` define the palette, typefaces and
layout. Load a stylesheet after the extension's CSS instead of modifying it:

..  code-block:: typoscript

    page.includeCSS.projectManual = EXT:site_package/Resources/Public/Css/manual.css

For example, that file can contain:

..  code-block:: css

    :root {
        --manual-font-serif: Georgia, serif;
        --manual-sidebar-width: 250px;
        --manual-aside-width: 210px;
        --manual-radius: 0;
    }

Important token groups:

..  list-table::
    :header-rows: 1

    *   - Tokens
        - Purpose
    *   - ``--manual-primary``
        - Lead colour; prefer the site setting so related shades follow it
    *   - ``--manual-plum``, ``--manual-plum-light``
        - Editorial accents and backgrounds
    *   - ``--manual-paper``, ``--manual-surface``, ``--manual-line``
        - Page, cards and borders
    *   - ``--manual-text``, ``--manual-text-muted``, ``--manual-text-soft``
        - Text hierarchy
    *   - ``--manual-font-sans``, ``--manual-font-serif``, ``--manual-font-mono``
        - Font stacks
    *   - ``--manual-sidebar-width``, ``--manual-aside-width``
        - Navigation and contents columns

The stylesheet has dark-mode overrides for system preference and
``:root[data-theme="dark"]``; explicit light mode uses ``data-theme="light"``.
If you change surface/text colours, cover those modes too and check contrast.
The shipped fonts are self-hosted and include their SIL Open Font License files.

Fluid overrides
===============

The shell uses ``page.10`` (FLUIDTEMPLATE). Add later paths in your project
TypoScript, loaded after the manual set:

..  code-block:: typoscript

    page.10 {
      templateRootPaths.100 = EXT:site_package/Resources/Private/Manual/Templates/
      partialRootPaths.100 = EXT:site_package/Resources/Private/Manual/Partials/
    }

    lib.contentElement {
      templateRootPaths.200 = EXT:site_package/Resources/Private/Manual/ContentTemplates/
      partialRootPaths.200 = EXT:site_package/Resources/Private/Manual/ContentPartials/
    }

Copy only the template or partial you need. The shell template is
:file:`Resources/Private/Templates/Default.html`; dedicated content templates are
in :file:`Resources/Private/fluid_styled_content/`.

Preserve the heading IDs and data attributes used by navigation and the table of
contents. Keep record-based text rendering and the optional visual-editor
content-area partial if inline editing is needed. Test the printable page too:
``pdf < page`` is a TypoScript copy, so later overrides of ``page`` may need an
explicit equivalent override of ``pdf``.
