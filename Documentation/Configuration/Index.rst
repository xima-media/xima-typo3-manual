..  _configuration:

=============
Configuration
=============

The site set ``xima/manual`` includes the dependencies
``typo3/fluid-styled-content`` and ``typo3/indexed-search``. It imports the
focus-point rendering and configures the manual's four content types, RTE,
frontend shell and printable page type.

Use the site settings for per-manual appearance and behaviour. There is no
extension-manager settings form for these options.

..  toctree::
    :maxdepth: 1
    :titlesonly:

    SiteSettings
    Access

Legacy static includes
======================

Existing installations can keep the static TypoScript template
:guilabel:`XIMA Manual` and the static page TSconfig :guilabel:`XIMA Manual`.
Their entry points are:

*   :file:`Configuration/TypoScript/constants.typoscript`
*   :file:`Configuration/TypoScript/setup.typoscript`
*   :file:`Configuration/TSconfig/Page.tsconfig`

For new installations use the site set, not both mechanisms. The static
TypoScript constants expose the appearance and full-manual options; FAQ and
changelog targets are read from the site's settings by
``ManualSettingsProcessor``.

Related-record list
===================

The related-record selector and coverage report use the same
``RecordTypeRegistry``. To leave additional tables out, configure a list in
:file:`ext_localconf.php` of a project extension:

..  code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['xima_typo3_manual']
        ['relations']['additionalTablesToSkip'] = ['tx_example_domain_model_log'];

This is a presentation filter for the selector/report, not an authorization
mechanism. On TYPO3 13, ``list_type`` plugins are listed separately and stay in
the list even if ``tt_content`` is skipped.
