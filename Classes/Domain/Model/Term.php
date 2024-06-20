<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Term extends AbstractEntity
{
    protected string $title = '';
    protected string $description = '';
    protected string $synonyms = '';
    protected string $link = '';

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

    public function getSynonyms(): string
    {
        return $this->synonyms;
    }

    public function setSynonyms(string $synonyms): void
    {
        $this->synonyms = $synonyms;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }
}
