<?php

namespace Xima\XimaTypo3Manual\Service;

use DOMDocument;
use DOMXPath;
use TYPO3\CMS\Core\SingletonInterface;
use Xima\XimaTypo3Manual\Model\Repository\GlossaryRepository;

class ContentParser implements SingletonInterface
{
    /**
     * ToDo: Make these configurable
     */
    private static array $ignoreParentTags = [
        'head',
        'a',
        'img',
        'script',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
    ];
    private static array $restrictedParentClasses = [
        'frame-type-mtext',
        'frame-type-mbox',
        'frame-type-bw_focuspoint_images_svg',
    ];

    public function __construct(protected GlossaryRepository $glossaryRepository)
    {
    }

    /**
     * @throws \DOMException
     */
    public function process(string $html): string
    {
        /**
         * ToDo:
         * - disable the parser globally
         * - disable the TYPO3 glossary for certain pages
         * - caching for the glossary entries?
         * - respect case-sensitive and synonyms!
         */
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $nodes = $this->fetchDomTags($dom);
        $glossaryEntries = $this->glossaryRepository->findAll();

        foreach ($nodes as $node) {
            foreach ($glossaryEntries as $entry) {
                $term = $entry['term'];
                $description = $entry['description'];

                if (strpos($node->nodeValue, $term) !== false) {
                    // Split the node's text around the term
                    $parts = explode($term, $node->nodeValue);

                    // Create a new fragment to hold the new nodes
                    $fragment = $dom->createDocumentFragment();

                    foreach ($parts as $i => $part) {
                        // Add the text part to the fragment
                        $fragment->appendChild($dom->createTextNode($part));

                        // If this is not the last part, add a 'dfn' element
                        if ($i < count($parts) - 1) {
                            $newElement = $dom->createElement('dfn', $term);
                            $newElement->setAttribute('title', $description);
                            $newElement->setAttribute('class', 'xima-typo3-manual--glossary');
                            $fragment->appendChild($newElement);
                        }
                    }

                    // Replace the original node with the fragment
                    if ($node->parentNode) {
                        $node->parentNode->replaceChild($fragment, $node);
                    }
                }
            }
        }

        return $dom->saveHTML();
    }

    protected function fetchDomTags(DOMDocument $dom): \DOMNodeList
    {
        $xpath = new DOMXPath($dom);

        // Generate the XPath query string
        $query = '//text()[(';

        // Only regard manual page content elements
        foreach (self::$restrictedParentClasses as $i => $class) {
            $query .= "ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
            if ($i < count(self::$restrictedParentClasses) - 1) {
                $query .= ' or ';
            }
        }
        $query .= ') and not(';
        // Ignore certain parent tags
        foreach (self::$ignoreParentTags as $i => $tag) {
            $query .= "ancestor::$tag";
            if ($i < count(self::$ignoreParentTags) - 1) {
                $query .= ' or ';
            }
        }
        $query .= ')]';

        return $xpath->query($query);
    }
}
