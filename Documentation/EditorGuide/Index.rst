..  _editor-guide:

============
Editor guide
============

Chapters are pages of type :guilabel:`Manual page`. Create them below the manual
root in the page tree, then add content in the normal column of the page module
(:guilabel:`Layout` on TYPO3 14). Sorting the pages changes the reading order.
Nested pages become nested chapters. The navigation processor reads up to nine
levels.

The root is the overview, not an ordinary chapter. Its title and subtitle form
the hero; its child pages form the overview cards. Put introductory instructions
in a child chapter. A chapter without content falls back to a list of subpages.
Pages hidden in menus do not appear in the chapter navigation or overview.

..  toctree::
    :maxdepth: 1
    :titlesonly:

    ContentElements
    RelatedRecords

Reading and previewing
======================

Use :guilabel:`Preview manual` from a manual page or open the
:guilabel:`Editor Manual` module. The docheader offers the frontend preview,
PDF download and coverage report. Follow the language selector for translated
manuals. Standard TYPO3 page/content translations are used; configure the site
languages first.

The chapter tree remembers expanded branches in the browser. The colour-scheme
buttons switch between automatic (system), light and dark rendering. The backend
module passes the backend user's colour scheme to the embedded frontend.
