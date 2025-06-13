<?php

namespace Xima\XimaTypo3Manual\Generator\Preset;

interface PresetInterface
{
    public function __construct(int $pid);

    public function getIdentifier(): string;

    public function getTitle(): string;

    public function getDescription(): string;

    public function getData(): array;
}
