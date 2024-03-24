# XIMA TYPO3 Manual Extension

This extension introduces a dedicated page type within TYPO3 backend, specifically designed for the creation of editor manuals. Administrators can easily craft user guides directly in the backend.

![Backend Preview](./Documentation/Images/backend_preview.png)

## Features

* Backend module with preview
* Seamless integration: Associate individual chapters to TYPO3 records for easy access
* PDF download
* Annotate screenshots with image editor: See [bw_focuspoint_images](https://extensions.typo3.org/extension/bw_focuspoint_images)
* TYPO3 system icons available in RTE: See [bw_icons](https://extensions.typo3.org/extension/bw_icons)

## Installation

1. Install via composer

   ```bash
   composer require xima/xima-typo3-manual
   ```

2. Create a new page in the page tree
   * Select Type "**Manual page**"
   * Check "**Use as Root Page**"
   * Include **static PageTS** "XIMA Manual"

3. Create new **Root-TypoScript** template for this page + include static TypoScript of this extension

## License

This project is licensed under [GNU General Public License 2.0 (or later)](LICENSE.md).
