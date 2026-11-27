..  _installation:

============
Installation
============

Requirements
============

..  list-table::
    :header-rows: 1

    *   - Dependency
        - Supported versions for 3.0
    *   - TYPO3
        - ``^13.4.29 || ^14.3``
    *   - PHP
        - ``^8.2`` (also observe the selected TYPO3 version's requirements)
    *   - Required system extensions
        - ``filelist``, ``fluid_styled_content``, ``indexed_search``,
          ``rte_ckeditor``
    *   - bw_focuspoint_images
        - ``^6.0 || ^7.0``
    *   - bw_icons
        - ``^4.0 || ^5.0``
    *   - dompdf/dompdf
        - ``^2.0 || ^3.0``

On TYPO3 14 use dependency versions that support 14, in particular bw_icons 5.x.
The required PHP extensions include ``dom``, ``fileinfo`` and ``simplexml``.

Composer installation
=====================

..  code-block:: bash

    composer require xima/xima-typo3-manual

Apply the database schema changes through TYPO3's maintenance tools. This also
creates the indexed-search tables. Flush the TYPO3 caches.

Composer is the recommended installation method. The extension is listed in the
`TYPO3 Extension Repository
<https://extensions.typo3.org/extension/xima_typo3_manual>`__, but a classic
installation also needs the non-core Composer library dompdf; see
:ref:`known-problems`.

First manual: installation wizard
=================================

As an administrator, open :guilabel:`Editor Manual` in the backend. When there is
no accessible manual, the installation screen offers the available presets.
:guilabel:`Empty Manual` creates a new top-level manual root and a site with
``xima/manual`` attached. It does not create sample chapters.

Check the generated site's base and languages, then add chapters underneath its
root. Only administrators may create a manual through the wizard.

Manual setup
============

Alternatively:

1.  Create a top-level page of type :guilabel:`Manual page`.
2.  Enable :guilabel:`Use as Root Page` in its page properties.
3.  Create a site configuration whose root is that page.
4.  Add the dependency :guilabel:`XIMA Manual` (``xima/manual``).
5.  Give the site a base URL and at least one language.
6.  Create child pages of type :guilabel:`Manual page` and add content to them.

The site dependency in :file:`config/sites/<identifier>/config.yaml` is:

..  code-block:: yaml

    dependencies:
      - xima/manual

The manual root's page title is the visible manual title; its subtitle is the
introductory text on the overview. Grant editors the module and page access they
need; see :ref:`access`.

Do not combine this set with an unrelated frontend site package on the same
manual site: it defines the ``page`` PAGE object and dedicated content templates.
