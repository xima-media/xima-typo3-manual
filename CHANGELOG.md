# Changelog

All notable changes to this project are documented in this file. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), the project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - unreleased

### Added

- Support for TYPO3 v14.3 alongside v13.4
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
