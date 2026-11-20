<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Service;

use TYPO3\CMS\Core\Localization\LanguageService;
use Xima\XimaTypo3Manual\Configuration;
use Xima\XimaTypo3Manual\Domain\RecordType;

/**
 * Enumerates everything a manual chapter can be linked to. Shared by the TCA item list of
 * tx_ximatypo3manual_relations and by the coverage report, so both always show the same set.
 */
class RecordTypeRegistry
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
     * @return list<RecordType>
     */
    public function getAll(): array
    {
        $types = [];
        $tablesToSkip = $this->getTablesToSkip();
        $otherGroup = $this->translate(Configuration::LANGUAGE_FILE . 'tx_ximatypo3manual_relation.other');

        foreach (array_keys($GLOBALS['TCA'] ?? []) as $tableName) {
            $table = (string)$tableName;
            if (in_array($table, $tablesToSkip, true)) {
                continue;
            }

            $tcaTypes = $GLOBALS['TCA'][$table]['types'] ?? [];
            $tableTitle = $this->translate((string)($GLOBALS['TCA'][$table]['ctrl']['title'] ?? $table));

            foreach (array_keys($tcaTypes) as $type) {
                $types[] = new RecordType(
                    $table . ':' . $type,
                    $table,
                    $this->translate($this->getLabel($table, $type)),
                    count($tcaTypes) > 1 ? $tableTitle : $otherGroup,
                    $this->getIcon($table, $type),
                );
            }
        }

        return [...$types, ...$this->getPlugins()];
    }

    /**
     * TYPO3 v14 removed list_type, every plugin is a CType there and therefore already covered by the table loop.
     *
     * @return list<RecordType>
     */
    private function getPlugins(): array
    {
        $plugins = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] ?? [] as $item) {
            if (empty($item['value'])) {
                continue;
            }

            $plugins[] = new RecordType(
                'tt_content:list:' . $item['value'],
                'tt_content',
                $this->translate((string)($item['label'] ?? $item['value'])),
                'Plugin',
                (string)($item['icon'] ?? ''),
            );
        }

        return $plugins;
    }

    /**
     * @return list<string>
     */
    private function getTablesToSkip(): array
    {
        $additional = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['xima_typo3_manual']['relations']['additionalTablesToSkip'] ?? null;

        return is_array($additional)
            ? array_values([...self::TABLES_TO_SKIP, ...array_map(strval(...), $additional)])
            : self::TABLES_TO_SKIP;
    }

    private function getLabel(string $table, string|int $type): string
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

    private function getIcon(string $table, string|int $type): string
    {
        $iconName = (string)$type === '0' ? 'default' : $type;

        return (string)($GLOBALS['TCA'][$table]['ctrl']['typeicon_classes'][$iconName]
            ?? $GLOBALS['TCA'][$table]['ctrl']['iconfile']
            ?? '');
    }

    private function translate(string $label): string
    {
        $languageService = $GLOBALS['LANG'] ?? null;

        return $languageService instanceof LanguageService ? $languageService->sL($label) : $label;
    }
}
