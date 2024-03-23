<?php

namespace Xima\XimaTypo3Manual\UserFunctions;

use TYPO3\CMS\Core\Utility\MathUtility;

class SelectItemsProcFunc
{
    public static function getLabelForTableAndType(string $table, string|int $type): string
    {
        $fallbackLabel = $GLOBALS['TCA'][$table]['ctrl']['title'];

        $typeField = $GLOBALS['TCA'][$table]['ctrl']['type'] ?? null;
        if (!$typeField) {
            return $fallbackLabel;
        }

        $typeItems = $GLOBALS['TCA'][$table]['columns'][$typeField]['config']['items'] ?? [];
        if (empty($typeItems)) {
            return $fallbackLabel;
        }

        $index = array_search($type, array_column($typeItems, 'value'), false);
        if ($index) {
            $index = MathUtility::canBeInterpretedAsInteger($index) ? (int)$index : $index;
            return $typeItems[$index]['label'] ?? $fallbackLabel;
        }

        return $fallbackLabel;
    }

    public static function getIconForTableAndType(string $table, string|int $type): string
    {
        $iconName = $type === 0 ? 'default' : $type;
        if (isset($GLOBALS['TCA'][$table]['ctrl']['typeicon_classes'][$iconName])) {
            return $GLOBALS['TCA'][$table]['ctrl']['typeicon_classes'][$iconName];
        }

        return $GLOBALS['TCA'][$table]['ctrl']['iconfile'] ?? '';
    }

    public function getItems(&$params): void
    {
        $items = [];

        $tablesToSkip = [
            'sys_category',
            'be_groups',
            'be_users',
            'fe_groups',
            'fe_users',
            'sys_news',
            'backend_layout',
            'be_dashboards',
            'tx_impexp_presets',
            'sys_webhook',
            'tx_extensionmanager_domain_model_extension',
            'sys_file',
            'sys_file_metadata',
            'sys_file_reference',
            'sys_file_storage',
            'sys_filemounts',
            'sys_language',
            'sys_reaction',
            'sys_language_overlay',
            'sys_log',
            'sys_note',
            'sys_refindex',
            'sys_template',
            'sys_workspace',
            'sys_file_collection',
        ];

        $tables = array_keys($GLOBALS['TCA']);
        foreach ($tables as $table) {
            if (in_array($table, $tablesToSkip, true)) {
                continue;
            }
            foreach ($GLOBALS['TCA'][$table]['types'] as $type => $typeConfig) {
                $label = self::getLabelForTableAndType($table, $type);
                $typeCount = count($GLOBALS['TCA'][$table]['types']);

                $item = [
                    'value' => $table . ':' . $type,
                    'label' => $GLOBALS['LANG']->sL($label),
                    'icon' => self::getIconForTableAndType($table, $type),
                ];

                if ($typeCount > 1) {
                    $item['group'] = $GLOBALS['LANG']->sL($GLOBALS['TCA'][$table]['ctrl']['title']);
                } else {
                    $item['group'] = 'Other';
                }

                $items[] = $item;
            }
        }
        $params['items'] = $items;
    }
}
