<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Term extends AbstractEntity
{
    protected string $title = '';
    protected string $description = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
