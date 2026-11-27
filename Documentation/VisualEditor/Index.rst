..  _visual-editor:

=================================
Optional visual-editor integration
=================================

Install EXT:visual_editor to edit chapter headings and rich text inline in the
backend's visual-editing module:

..  code-block:: bash

    composer require friendsoftypo3/visual-editor

The manual declares support for the 1.x integration (``^1.10``). No additional
manual setting is needed. The extension only layers in the content-area partial
while EXT:visual_editor is loaded; without it the normal rendering remains
available.

On TYPO3 14 the module is :guilabel:`Content → Edit`. Open the chapter you want
to maintain. Text and heading edits target their own records, including records
in other visible chapters of a full-manual view.

..  important::
    Adding, moving and deleting elements use the **requested chapter** as the
    content area. When the full manual is visible, open the intended chapter in
    the visual editor before changing its structure. Seeing another chapter
    further down the page does not make it the current insertion target.

Use the standard backend forms for fields and structures that the inline editor
does not expose, such as related-record assignments and focus-point settings.
