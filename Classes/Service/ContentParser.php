<?php

namespace Xima\XimaTypo3Manual\Service;

use DOMDocument;
use DOMXPath;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use Xima\XimaTypo3Manual\Domain\Repository\TermRepository;

class ContentParser implements SingletonInterface
{
    protected array $configuration = [];

    public function __construct(protected TermRepository $termRepository, protected FrontendInterface $termCache)
    {
        $setup = $GLOBALS['TSFE']->tmpl->setup;
        if (!isset($setup['plugin.']['tx_ximatypo3manual.'])) {
            return;
        }
        $this->configuration = $setup['plugin.']['tx_ximatypo3manual.'];
    }

    /**
     * @throws \DOMException
     */
    public function process(string $html): string
    {
        /**
         * ToDo:
         * - disable the TYPO3 glossary for certain pages
         * - maybe respect case-sensitive
         */
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $nodes = $this->fetchDomTags($dom);
        $glossaryEntries = $this->getTerms();
        if (empty($glossaryEntries)) {
            return $html;
        }

        foreach ($nodes as $node) {
            // Skip empty nodes
            if (str_replace([' ', "\r", "\n", "\t"], '', $node->nodeValue) === '') {
                continue;
            }

            // Collect all terms that match the node's content
            $matchedTerms = [];
            foreach ($glossaryEntries as $entry) {
                $search = $entry->getTitle();
                if (str_contains($node->nodeValue, $search)) {
                    $matchedTerms[$search] = $entry;
                }
                if ($entry->getSynonyms() !== '') {
                    foreach (GeneralUtility::trimExplode(',', $entry->getSynonyms()) as $synonym) {
                        if (str_contains($node->nodeValue, $synonym)) {
                            $matchedTerms[$synonym] = $entry;
                        }
                    }
                }
            }
            if (!empty($matchedTerms)) {
                $this->checkNodeByMatchedTerms($dom, $node, $matchedTerms);
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
        $restrictedParentClasses = explode(',', $this->configuration['settings.']['restrictedParentClasses']);
        foreach ($restrictedParentClasses as $i => $class) {
            $query .= "ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
            if ($i < count($restrictedParentClasses) - 1) {
                $query .= ' or ';
            }
        }
        $query .= ') and not(';
        // Ignore certain parent tags
        $ignoreParentTags = explode(',', $this->configuration['settings.']['ignoreParentTags']);
        foreach ($ignoreParentTags as $i => $tag) {
            $query .= "ancestor::$tag";
            if ($i < count($ignoreParentTags) - 1) {
                $query .= ' or ';
            }
        }
        $query .= ')]';

        return $xpath->query($query);
    }

    protected function getTerms(): array
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);

        $querySettings->setStoragePageIds(
            GeneralUtility::trimExplode(
                ',',
                $this->configuration['persistence.']['storagePid'] ?? ''
            )
        );
        $querySettings->setRespectStoragePage((bool)$this->configuration['persistence.']['storagePid']);

        $context = GeneralUtility::makeInstance(Context::class);
        $languageAspect = $context->getAspect('language');
        $querySettings->setLanguageAspect($languageAspect);
        $querySettings->setRespectSysLanguage(true);
        $this->termRepository->setDefaultQuerySettings($querySettings);

        if (!($this->configuration['settings.']['useGlossaryTermCache'] ?? true)) {
            $terms = $this->termRepository->findAll();
        } else {
            $cacheIdentifierParts = [
                'glossarytermcache',
                $this->configuration['persistence.']['storagePid'],
                GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId(),
            ];
            $cacheIdentifier = sha1(implode('-', $cacheIdentifierParts));
            $terms = $this->termCache->get($cacheIdentifier) ?: [];

            if (empty($this->terms)) {
                $terms = $this->termRepository->findAll()->toArray();
                $this->termCache->set($cacheIdentifier, $terms, ['ximatypo3manual_glossarytermcache']);
            }
        }
        return $terms;
    }

    protected function checkNodeByMatchedTerms(DOMDocument $dom, mixed &$node, array $matchedTerms): void
    {
        // Split the node's text around the matched terms
        $escapedSeparators = array_map('preg_quote', array_keys($matchedTerms));
        $pattern = '/(' . implode('|', $escapedSeparators) . ')/i';
        preg_match_all($pattern, $node->nodeValue, $parts, PREG_OFFSET_CAPTURE);

        // Create a new fragment to hold the new nodes
        $fragment = $dom->createDocumentFragment();

        $lastPosition = 0;
        foreach ($parts[0] as $part) {
            $position = $part[1];
            $matchedTerm = $part[0];

            // add segment before the matched term
            if ($position > $lastPosition) {
                $fragment->appendChild($dom->createTextNode(substr($node->nodeValue, $lastPosition, $position - $lastPosition)));
            }

            // Add matched term
            $newElement = $dom->createElement('dfn', $matchedTerm);
            $newElement->setAttribute('data-tooltip', $matchedTerms[$matchedTerm]->getDescription());
            $newElement->setAttribute('class', 'xima-typo3-manual--glossary');
            $fragment->appendChild($newElement);
            $lastPosition = $position + strlen($matchedTerm);
        }

        // add segment after the last matched term
        if ($lastPosition < strlen($node->nodeValue)) {
            $fragment->appendChild($dom->createTextNode(substr($node->nodeValue, $lastPosition)));
        }

        // Replace the original node with the fragment
        if ($node->parentNode) {
            $node->parentNode->replaceChild($fragment, $node);
        }
    }
}
