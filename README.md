# XIMA TYPO3 Manual Extension

This extension provides a new page type for creating an editor manual right in the TYPO3 backend.

![Backend Preview](./Documentation/Images/backend_preview.png)

## Features

* Backend module with preview
* PDF download
* Configured [bw_focuspoint_images](https://extensions.typo3.org/extension/bw_focuspoint_images) to annotate screenshots
* Configured [bw_icons](https://extensions.typo3.org/extension/bw_icons) to add inline icons

## Installation

1. Install via composer

   ```bash
   composer require xima/xima-typo3-manual
   ```

2. Create a new page in the page tree
   * Select doctype "Manual page"
   * Check "Use as Root Page"
   * Include static PageTS

3. Create new Root-TypoScript template for this page + include static TypoScript of this extension

## Transferring the manual

An initial transfer can be done with the TYPO3 integrated [ImpExp extension](https://docs.typo3.org/c/typo3/cms-impexp/main/en-us/). However, updating an existing page tree is not recommend - better wait for the upcoming extension [xima-typo3-page-sync](https://github.com/xima-media/xima-typo3-page-sync).

### Export

To export the pagetree of the manual, you could use the following command:

```
typo3cms impexp:export --type t3d_compressed --levels 999 --table _ALL --include-related --include-static sys_file_storage _ALL --pid <UID>
```

### Import

```
typo3cms impexp:import --update-records  fileadmin/user_upload/_temp_/importexport/<FILENAME>.t3d
```
