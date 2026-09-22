<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual;

/**
 * Central constants of the manual: the page type it introduces, its content element types and the page type used to
 * render the printable version.
 */
final class Configuration
{
    public const EXTENSION_KEY = 'xima_typo3_manual';

    public const DOKTYPE_MANUAL = 701;

    public const PDF_PAGE_TYPE = 1664618986;

    public const SITE_SET = 'xima/manual';

    public const LANGUAGE_FILE = 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:';

    /**
     * @var non-empty-list<string>
     */
    public const CONTENT_TYPES = ['mtext', 'mbox', 'msteps', 'mannotation'];

    /**
     * Tables a manual page may hold. Also used as TCA "allowedRecordTypes" on TYPO3 v14.
     *
     * @var non-empty-list<string>
     */
    public const ALLOWED_RECORD_TYPES = ['pages', 'tt_content', 'sys_template', 'sys_file_reference'];
}
