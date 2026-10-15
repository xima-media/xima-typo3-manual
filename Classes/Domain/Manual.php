<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Domain;

/**
 * One manual of the installation, identified by the root page of its page tree.
 */
final readonly class Manual
{
    public function __construct(
        public int $uid,
        public string $title,
    ) {
    }
}
