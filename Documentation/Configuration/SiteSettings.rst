..  _site-settings:

=============
Site settings
=============

With ``xima/manual`` active, edit these per-site values in the site's settings
editor (the :guilabel:`Sites` area; labels differ between TYPO3 13 and 14).
The definitions live in
:file:`Configuration/Sets/Manual/settings.definitions.yaml`.

Appearance
==========

..  confval:: manual.appearance.primaryColor
    :type: color
    :default: ``#1A5FB4``

    Primary colour for links, markers and active states. The processor emits
    ``--manual-primary`` through TYPO3's asset collector. This is independent of
    the backend login highlight colour.

..  confval:: manual.appearance.eyebrow
    :type: string
    :default: ``''``

    Short line above the manual title. An empty value uses the translated
    "Editor manual" label.

..  confval:: manual.appearance.showNavigation
    :type: bool
    :default: ``true``

    Display the chapter navigation sidebar.

..  confval:: manual.appearance.showToc
    :type: bool
    :default: ``true``

    Display the contents / "on this page" column where applicable. Individual
    chapter views only need it when there are enough headings.

..  confval:: manual.appearance.showContentInNavigation
    :type: bool
    :default: ``false``

    Include content-element headers in the chapter tree. To resemble the former
    navigation, enable this and disable ``showToc``.

Behaviour
=========

..  confval:: manual.behaviour.displayFullManual
    :type: bool
    :default: ``true``

    Render all chapters on one continuous page. With ``false``, each chapter
    has its own page and the chapter tree links to URLs instead of anchors.
    The backend PDF export always renders the full manual.

Quicklinks
==========

..  confval:: manual.navigation.faqPage
    :type: page
    :default: ``0``

    Target for the FAQ quicklink. ``0`` hides it. The selected page must exist
    and not be hidden/deleted. Choose a page in this manual. Its contents are
    presented as entries rather than a normal chapter card.

..  confval:: manual.navigation.changelogPage
    :type: page
    :default: ``0``

    Target for the changelog quicklink, with the same rules as ``faqPage``.
    Leave it at ``0`` to hide the link.

Example configuration
=====================

For a manually maintained :file:`config/sites/<identifier>/settings.yaml`:

..  code-block:: yaml

    manual:
      appearance:
        primaryColor: '#1A5FB4'
        eyebrow: 'Website handbook'
        showNavigation: true
        showToc: true
        showContentInNavigation: false
      behaviour:
        displayFullManual: false
      navigation:
        faqPage: 0
        changelogPage: 0

The site set also sets the Fluid Styled Content template/partial paths and image
width defaults (``styles.content.textmedia.maxW: 1200`` and ``maxWInText: 640``).
Override those only when you intentionally replace the content rendering.
