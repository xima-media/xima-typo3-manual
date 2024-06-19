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
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);

        $setup = $GLOBALS['TSFE']->tmpl->setup;
        $this->configuration = $setup['plugin.']['tx_ximatypo3manual.'];

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
    }

    /**
     * @throws \DOMException
     */
    public function process(string $html): string
    {
        /**
         * ToDo:
         * - disable the TYPO3 glossary for certain pages
         * - respect case-sensitive and synonyms!
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
            if (str_replace([" ", "\r", "\n"], '', $node->nodeValue) === '') {
                continue;
            }
            foreach ($glossaryEntries as $entry) {
                $term = $entry->getTitle();
                $description = $entry->getDescription();

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
                            $newElement->setAttribute('data-tooltip', $description);
                            $newElement->setAttribute('class', 'xima-typo3-manual--glossary simptip-position-top simptip-multiline');
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
        $restrictedParentClasses = explode(',',$this->configuration['settings.']['restrictedParentClasses']);
        foreach ($restrictedParentClasses as $i => $class) {
            $query .= "ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
            if ($i < count($restrictedParentClasses) - 1) {
                $query .= ' or ';
            }
        }
        $query .= ') and not(';
        // Ignore certain parent tags
        $ignoreParentTags = explode(',',$this->configuration['settings.']['ignoreParentTags']);
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
        if (!($this->configuration['settings.']['useGlossaryTermCache'] ?? true)) {
            $terms = $this->termRepository->findAll();
        } else {
            $cacheIdentifierParts = [
                'glossarytermcache',
                $this->configuration['persistence.']['storagePid'],
                GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId()
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
}
