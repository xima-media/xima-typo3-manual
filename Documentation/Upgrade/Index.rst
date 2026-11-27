..  _upgrade:

======================
Upgrade from 2.x to 3.0
======================

Read the repository's `changelog
<https://github.com/xima-media/xima-typo3-manual/blob/main/CHANGELOG.md>`__ for the
full list. Back up the database, files and site configuration before updating.

Requirements and configuration
==============================

1.  Upgrade TYPO3 to at least 13.4.29 or use 14.3, with PHP 8.2 or newer. TYPO3
    12 is no longer supported.
2.  Update the package and its dependencies. ``filelist``,
    ``fluid_styled_content``, ``indexed_search`` and ``rte_ckeditor`` are required.
    On TYPO3 14, use bw_icons 5.x.
3.  Apply the database compare, including indexed-search tables. Run applicable
    upgrade wizards and flush all caches. The annotation wizard
    ``ximaManual_mAnnotationsUpgradeWizard`` migrates legacy
    ``bw_focuspoint_images_svg`` elements inside manual pages to ``mannotation``;
    it is for older content and may have nothing left to do on a 2.x install.
4.  Add ``xima/manual`` to each manual site, replacing duplicate static includes,
    **or** keep the existing static TypoScript template and page TSconfig.
5.  Set ``manual.appearance.primaryColor`` per site if the former backend login
    highlight colour should be retained. With the static template, set the
    constant in the template's constants instead: the static template assigns a
    default that overrides the site setting.
6.  Rebuild the search index as described in :ref:`search`, including every
    chapter URL in one-chapter-per-page mode. Check the module, related-record
    links, print view and PDF download.

Content and design
==================

The frontend was redesigned. Rework custom template, partial and stylesheet
overrides written for the 2.x Gutenberg styles, ``.ce-mbox`` markup or the old
step carousel. Do not carry removed asset files into the new templates.

*   ``mbox`` now has a rich-text body; existing plain text is rendered as
    paragraphs. The default layout is the editorial note style.
*   ``msteps`` defaults to a numbered roadmap. The former carousel behaviour is
    the slider layout, not the default.
*   Chapters and content headings occupy separate navigation/contents columns.
    ``showContentInNavigation: true`` and ``showToc: false`` approximate the
    previous navigation behaviour.
*   ``DataProcessing\BackendSettingsProcessor`` was removed. The current
    processor is ``ManualSettingsProcessor``; check any custom TypoScript that
    referenced the old class.
*   The removed ``plugin.tx_bwfocuspointimages.view`` constants no longer provide
    a manual template override. The annotation element has its own template.

Custom presets
==============

``PresetInterface`` implementations no longer receive the page ID in a
constructor. Change ``getData()`` to ``getData(int $pid): array`` and implement
``getIconIdentifier(): string``. Presets are services collected from the
container via the ``xima_typo3_manual.preset`` tag, not manually instantiated.
See :ref:`custom-presets`.

Security and exposure
=====================

The PDF backend download now requires a backend login and page read access.
That does **not** make the frontend manual or its printable URL private. Review
web-server access rules separately; see :ref:`access` and :ref:`pdf-export`.
