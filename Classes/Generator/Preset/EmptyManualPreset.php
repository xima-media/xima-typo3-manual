<?php

namespace Xima\XimaTypo3Manual\Generator\Preset;

class EmptyManualPreset implements PresetInterface
{
    public function __construct(protected int $pid)
    {
    }

    public function getIdentifier(): string
    {
        return '1';
    }

    public function getDescription(): string
    {
        return 'This is a demo manual with no content. Start building your manual by adding pages and content.';
    }

    public function getData(): array
    {
        return [
            'pages' => [
                'NEW1' => [
                    'pid' => $this->pid,
                    'hidden' => 0,
                    'title' => $this->getTitle(),
                    'doktype' => 701,
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

    public function getTitle(): string
    {
        return 'Demo Manual';
    }
}
