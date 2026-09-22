# Changelog

All notable changes to this project are documented in this file. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), the project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - unreleased

### Changed

- **New design.** Calm blue as the lead colour, plum as the accent for editorial contexts, serif headings and
straight edges instead of rounded cards. The design tokens live as custom properties at the top of
`Resources/Public/Css/Frontend/manual.css`
- The manual renders in a three column shell: chapter tree, content, "on this page"
- Chapters are cards, the manual opens with a hero carrying its title
- The note element (`mbox`) became the design's note block with an info, editorial, warning, success and error
variant; its heading is rendered inside the block
- The step element (`msteps`) is a numbered list with a connector line instead of a carousel
- The glossary is a two column definition list
- Search results replace the chapter tree while a query is active and are shown as result cards
- The chapter tree remembers its open branches per browser and shows the number of entries per branch

### Added

- Optional integration of `friendsoftypo3/visual-editor`: with the extension installed, chapter headings and texts
are editable inline in the backend. The ViewHelper it registers is used through a partial that is only layered in
while the extension is loaded, so the manual keeps working without it
- Source Sans 3 and Source Serif 4 are shipped with the extension (SIL Open Font License), so an installation
makes no request to a third party for them
- An "on this page" column that follows the chapter currently in view

### Fixed

- Rich text was not editable in the visual editor: the icon picker module was declared without its exports, and the
  CKEditor bootstrap iterates them, so every editor instance aborted with "c is not iterable"
- Adding and moving elements was disabled in the visual editor, because the content area of the requested chapter was
  never marked: the template passed the current page id to a Fluid section that never received it
- Text elements failed to render in the visual editor. The RTE preset pulled in
`EXT:bw_icons/Configuration/RTE/IconPicker.yaml`, which declares its CKEditor module as a plain string; TYPO3
accepts that, the visual editor reads every entry as an array. The module is now declared in the documented form
- The annotation element was the one content element left in the old styling: its markers, its description list and
its heading now follow the design, and the marker colour comes from the design palette instead of TYPO3 orange
- Annotation descriptions had lost their spacing, because their stylesheet still referenced a custom property of the
removed Gutenberg stylesheets
- Picking a file for an image or annotation element failed with "Element browser with identifier file is not
registered": the manual uses file relations, but never declared its dependency on EXT:filelist

### Removed

- The content slider of the step element, together with `Slider.js` and `slider.css`
- The Gutenberg stylesheets, replaced by the design system stylesheet

## [2.1.0] - unreleased

### Added

- Support for TYPO3 v14.3 alongside v13.4
- Site set `xima/manual`, so a manual no longer needs a TypoScript record. The static template and the static page
TSconfig stay available for existing installations
- Full text search over the manual, rendered into the chapter navigation and answered in the browser
- Glossary content element with its own term elements. Every term is linked automatically wherever it appears in
the manual and carries its definition as a tooltip
- Documentation coverage report listing every record type next to the chapters documenting it, reachable from the
doc header of the manual module
- `Configuration/ContentSecurityPolicies.php`; the only inline stylesheet of the manual is registered through the
asset collector so TYPO3 can attach a nonce
- Presets for the installation wizard are collected from the service container, so a project can ship its own
by implementing `PresetInterface`
- Functional tests for the manual generator, the button bar event listener and the PDF middleware
- PHPStan runs on level 8 without a baseline

### Changed

- The extension requires TYPO3 v13.4 as a minimum, support for v12 was dropped
- `ext_tables.php` was removed; the manual page type is registered through TCA (`allowedRecordTypes`) on v14 and
through `PageDoktypeRegistry` on v13
- Event listeners are registered via `#[AsEventListener]` instead of `Services.yaml`
- `StandaloneView`, `LanguageService::includeLLFile()`, `$GLOBALS['TSFE']` and the deprecated `ButtonBar::make*()`
factories were replaced by their supported counterparts
- The user TSconfig option `options.pageTree.doktypesToShowInNewPageDragArea` is only set on v13, where it is not
yet deprecated, and moved to `Configuration/user.tsconfig`
- The PDF filename is derived from the manual title instead of being hardcoded to `Handbuch.pdf`
- Modal titles in the backend JavaScript are translatable
- New manuals are created with the site set attached instead of a TypoScript record
- The record types a chapter can document are enumerated by `RecordTypeRegistry`, shared by the TCA field and the
coverage report

### Fixed

- The backend module was unusable on TYPO3 v14 (fatal error in `LanguageService::includeLLFile()`), as were the
content element previews and the installation wizard
- The PDF download route was reachable without a backend login and without a page permission check
- Chapter lookups for the doc header button were built as raw SQL from a request parameter and are now issued
through the query builder
- Inline SVGs in the PDF export were appended next to the original element instead of replacing it, and only the
first element of a live node list was processed
- Assets for the PDF export are resolved inside the public path only
- The annotation upgrade wizard no longer issues an `IN ()` statement when there is nothing to migrate
- The installation wizard crashed on a missing `context` argument and rendered the wrong template for
non-admin users
- Manual elements are matched against the page type when a page is open, instead of reading the page id as a
content element id
- Functional tests never ran: the PHPUnit bootstrap path was wrong, the fixtures were not in the format the
testing framework expects and the tests still used `@test` annotations

## [2.0.5] and earlier

See the [release notes](https://github.com/xima-media/xima-typo3-manual/releases).
