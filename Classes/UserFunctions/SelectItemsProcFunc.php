<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\UserFunctions;

use Xima\XimaTypo3Manual\Service\RecordTypeRegistry;

/**
 * Builds the checkbox list of "what does this chapter document?".
 */
class SelectItemsProcFunc
{
    public function __construct(
        private readonly RecordTypeRegistry $recordTypeRegistry
    ) {
    }

    /**
     * @param array{items?: list<array<string, mixed>>} $params
     */
    public function getItems(array &$params): void
    {
        $items = [];
        foreach ($this->recordTypeRegistry->getAll() as $recordType) {
            $items[] = [
                'value' => $recordType->identifier,
                'label' => $recordType->label,
                'icon' => $recordType->icon,
                'group' => $recordType->group,
            ];
        }

        $params['items'] = $items;
    }
}
