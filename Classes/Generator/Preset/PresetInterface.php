<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Generator\Preset;

/**
 * A blueprint for a new manual. Implementations are collected automatically, so a project can ship its own preset by
 * implementing this interface.
 */
interface PresetInterface
{
    public function getIdentifier(): string;

    public function getTitle(): string;

    public function getDescription(): string;

    public function getIconIdentifier(): string;

    /**
     * DataHandler datamap creating the manual. The page tree root has to use the placeholder id "NEW1".
     *
     * @param int $pid Negative uid of the page the new tree is inserted after, or 0 for the first position
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function getData(int $pid): array;
}
