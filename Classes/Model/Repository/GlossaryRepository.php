<?php

namespace Xima\XimaTypo3Manual\Model\Repository;

class GlossaryRepository
{
    /**
     * ToDo: This is just a demo repo
     */
    private static array $demoGlossaryEntries = [
        [
            'term' => 'TYPO3',
            'caseSensitive' => false,
            'synonyms' => ['typo3', 'Typo3'],
            'description' => 'TYPO3 is a free and open-source Web Content Management System written in PHP, which is highly flexible and feature-rich, allowing developers to build and manage websites and applications of varying complexity and size.',
        ],
    ];

    public function findAll(): array
    {
        return self::$demoGlossaryEntries;
    }
}
