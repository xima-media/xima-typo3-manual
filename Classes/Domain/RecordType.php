<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Domain;

/**
 * One documentable thing: a record type of a TCA table, or a plugin signature on TYPO3 v13.
 */
final readonly class RecordType
{
    /**
     * @param string $identifier The value stored in tx_ximatypo3manual_relations, e.g. "tt_content:textmedia"
     */
    public function __construct(
        public string $identifier,
        public string $table,
        public string $label,
        public string $group,
        public string $icon,
    ) {
    }
}
