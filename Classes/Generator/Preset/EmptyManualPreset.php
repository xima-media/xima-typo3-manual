<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Generator\Preset;

use Xima\XimaTypo3Manual\Configuration;

class EmptyManualPreset implements PresetInterface
{
    public function getIdentifier(): string
    {
        return 'empty';
    }

    public function getTitle(): string
    {
        return Configuration::LANGUAGE_FILE . 'installation.preset.empty.title';
    }

    public function getDescription(): string
    {
        return Configuration::LANGUAGE_FILE . 'installation.preset.empty.description';
    }

    public function getIconIdentifier(): string
    {
        return 'apps-pagetree-manual-root';
    }

    public function getData(int $pid): array
    {
        return [
            'pages' => [
                'NEW1' => [
                    'pid' => $pid,
                    'hidden' => 0,
                    'title' => 'Demo Manual',
                    'doktype' => Configuration::DOKTYPE_MANUAL,
                    'is_siteroot' => 1,
                    'tsconfig_includes' => 'EXT:xima_typo3_manual/Configuration/TSconfig/Page.tsconfig',
                    'backend_layout' => 'pagets__manualHomepage',
                ],
            ],
            'sys_template' => [
                'NEW2' => [
                    'pid' => 'NEW1',
                    'title' => 'NEW MANUAL',
                    'root' => 1,
                    'clear' => 3,
                    'include_static_file' => 'EXT:xima_typo3_manual/Configuration/TypoScript',
                ],
            ],
        ];
    }
}
