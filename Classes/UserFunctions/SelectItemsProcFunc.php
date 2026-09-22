<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\UserFunctions;

use TYPO3\CMS\Core\Localization\LanguageService;
use Xima\XimaTypo3Manual\Configuration;

/**
 * Builds the checkbox list of "what does this chapter document?": every record type of every TCA table, plus the
 * plugin signatures still registered via list_type on TYPO3 v13.
 */
class SelectItemsProcFunc
{
    /**
     * Tables that never describe editor-facing content and would only clutter the list.
     *
     * @var list<string>
     */
    private const TABLES_TO_SKIP = [
        'backend_layout',
        'be_dashboards',
        'be_groups',
        'be_users',
        'fe_groups',
        'fe_users',
        'sys_category',
        'sys_file',
        'sys_file_collection',
        'sys_file_metadata',
        'sys_file_reference',
        'sys_file_storage',
        'sys_filemounts',
        'sys_language',
        'sys_language_overlay',
        'sys_log',
        'sys_news',
        'sys_note',
        'sys_reaction',
        'sys_refindex',
        'sys_template',
        'sys_webhook',
        'sys_workspace',
        'tx_extensionmanager_domain_model_extension',
        'tx_impexp_presets',
    ];

    /**
     * @param array{items?: list<array<string, mixed>>} $params
     */
    public function getItems(array &$params): void
    {
        $items = [];
        $tablesToSkip = self::TABLES_TO_SKIP;
        $additional = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['xima_typo3_manual']['relations']['additionalTablesToSkip'] ?? null;
        if (is_array($additional)) {
            $tablesToSkip = array_merge($tablesToSkip, $additional);
        }

        foreach (array_keys($GLOBALS['TCA'] ?? []) as $tableName) {
            $table = (string)$tableName;
            if (in_array($table, $tablesToSkip, true)) {
                continue;
            }

            $types = $GLOBALS['TCA'][$table]['types'] ?? [];
            $tableTitle = $this->translate($GLOBALS['TCA'][$table]['ctrl']['title'] ?? $table);

            foreach (array_keys($types) as $type) {
                $items[] = [
                    'value' => $table . ':' . $type,
                    'label' => $this->translate($this->getLabelForTableAndType($table, $type)),
                    'icon' => $this->getIconForTableAndType($table, $type),
                    'group' => count($types) > 1
                        ? $tableTitle
                        : $this->translate(Configuration::LANGUAGE_FILE . 'tx_ximatypo3manual_relation.other'),
                ];
            }
        }

        $params['items'] = array_merge($items, $this->getPlugins());
    }

    private function getLabelForTableAndType(string $table, string|int $type): string
    {
        $fallbackLabel = (string)($GLOBALS['TCA'][$table]['ctrl']['title'] ?? $table);

        $typeField = $GLOBALS['TCA'][$table]['ctrl']['type'] ?? null;
        if (!is_string($typeField) || $typeField === '') {
            return $fallbackLabel;
        }

        $typeItems = $GLOBALS['TCA'][$table]['columns'][explode(':', $typeField)[0]]['config']['items'] ?? [];
        foreach ($typeItems as $item) {
            if (isset($item['value']) && (string)$item['value'] === (string)$type) {
                return (string)($item['label'] ?? $fallbackLabel);
            }
        }

        return $fallbackLabel;
    }

    private function getIconForTableAndType(string $table, string|int $type): string
    {
        $iconName = (string)$type === '0' ? 'default' : $type;

        return (string)($GLOBALS['TCA'][$table]['ctrl']['typeicon_classes'][$iconName]
            ?? $GLOBALS['TCA'][$table]['ctrl']['iconfile']
            ?? '');
    }

    /**
     * TYPO3 v14 removed list_type, every plugin is a CType there and therefore already covered by the table loop.
     *
     * @return list<array<string, mixed>>
     */
    private function getPlugins(): array
    {
        $pluginItems = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] ?? [] as $item) {
            if (empty($item['value'])) {
                continue;
            }

            $pluginItems[] = [
                'value' => 'tt_content:list:' . $item['value'],
                'label' => $this->translate((string)($item['label'] ?? $item['value'])),
                'icon' => (string)($item['icon'] ?? ''),
                'group' => 'Plugin',
            ];
        }

        return $pluginItems;
    }

    private function translate(string $label): string
    {
        return $this->getLanguageService()?->sL($label) ?? $label;
    }

    private function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
