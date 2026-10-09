<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\DataProcessing;

use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Finds the pages before and after the current one in reading order: the start page first, then every chapter
 * depth-first, as the navigation lists them. FAQ and changelog are left out, the navigation links them separately.
 *
 * Expects the menu tree of the MenuProcessor, configured via "menu" (default "pages").
 */
class PageNavigationProcessor implements DataProcessorInterface
{
    private const EXCLUDED_PAGE_SETTINGS = ['manual.navigation.faqPage', 'manual.navigation.changelogPage'];

    private const EXCLUDED_DOKTYPES = [PageRepository::DOKTYPE_SPACER, PageRepository::DOKTYPE_SYSFOLDER];

    /**
     * @param mixed[] $contentObjectConfiguration
     * @param mixed[] $processorConfiguration
     * @param mixed[] $processedData
     * @return mixed[]
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $site = $cObj->getRequest()->getAttribute('site');
        if (!$site instanceof Site) {
            return $processedData;
        }

        $rootPageId = $site->getRootPageId();
        $readingOrder = [
            [
                'uid' => $rootPageId,
                'title' => (string)$cObj->getData('leveltitle:0'),
                'link' => $cObj->createUrl(['parameter' => $rootPageId]),
            ],
        ];
        $menu = $processedData[$processorConfiguration['menu'] ?? 'pages'] ?? [];
        $this->collect(is_array($menu) ? $menu : [], $this->getExcludedPageIds($site), $readingOrder);

        $currentPageId = (int)($cObj->data['uid'] ?? 0);
        $position = array_search($currentPageId, array_column($readingOrder, 'uid'), true);
        if ($position === false) {
            return $processedData;
        }

        $navigation = array_filter([
            'previous' => $readingOrder[$position - 1] ?? null,
            'next' => $readingOrder[$position + 1] ?? null,
        ]);
        if ($navigation !== []) {
            $processedData[$processorConfiguration['as'] ?? 'pageNavigation'] = $navigation;
        }

        return $processedData;
    }

    /**
     * @param mixed[] $items
     * @param int[] $excludedPageIds
     * @param list<array{uid: int, title: string, link: string}> $readingOrder
     */
    private function collect(array $items, array $excludedPageIds, array &$readingOrder): void
    {
        foreach ($items as $item) {
            $uid = (int)($item['data']['uid'] ?? 0);
            if ($uid === 0
                || !empty($item['data']['nav_hide'])
                || in_array($uid, $excludedPageIds, true)
                || in_array((int)($item['data']['doktype'] ?? 0), self::EXCLUDED_DOKTYPES, true)
            ) {
                continue;
            }

            $readingOrder[] = [
                'uid' => $uid,
                'title' => (string)($item['title'] ?? ''),
                'link' => (string)($item['link'] ?? ''),
            ];
            $this->collect($item['children'] ?? [], $excludedPageIds, $readingOrder);
        }
    }

    /**
     * @return int[]
     */
    private function getExcludedPageIds(Site $site): array
    {
        $pageIds = [];
        foreach (self::EXCLUDED_PAGE_SETTINGS as $setting) {
            $pageId = (int)$site->getSettings()->get($setting, 0);
            if ($pageId > 0) {
                $pageIds[] = $pageId;
            }
        }

        return $pageIds;
    }
}
