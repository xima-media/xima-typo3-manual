..  _developer:

===============
Developer guide
===============

Local DDEV setup
================

..  code-block:: bash

    git clone https://github.com/xima-media/xima-typo3-manual.git
    cd xima-typo3-manual
    ddev start
    ddev composer install
    ddev init-typo3
    ddev restart

..  warning::
    ``ddev init-typo3`` drops all tables in the local DDEV database and imports
    fixtures. Run it only in a disposable development instance, never against an
    existing project database.

Use the project URL reported by ``ddev describe``, with ``/manual/`` for the
manual and ``/typo3/`` for the backend. The fixtures create an ``admin`` user
with password ``Passw0rd!``; there is no preconfigured editor user. Do not expose
this demo publicly with those credentials.

The restart after a fresh initialization lets DDEV provide its additional TYPO3
configuration. All PHP and Composer commands below run inside DDEV.

Tests and quality checks
========================

..  code-block:: bash

    ddev composer test
    ddev composer test:unit
    ddev composer test:functional
    ddev composer sca
    ddev composer ci:sca

``test`` runs both PHPUnit suites. Their configurations are
:file:`phpunit.unit.xml` and :file:`phpunit.functional.xml`. ``sca`` applies
formatters and runs analysis; ``ci:sca`` is check-only. Inspect the diff after
``sca``, including :file:`phpstan-baseline.neon`: the PHPStan script generates a
baseline and must not be used to hide a defect. Stage new files before the final
check because editorconfig's ``--git-only`` skips untracked files.

Individual commands declared in :file:`composer.json`:

..  code-block:: bash

    ddev composer php:lint
    ddev composer php:rector
    ddev composer php:fixer
    ddev composer php:stan
    ddev composer typoscript:lint
    ddev composer yaml:lint
    ddev composer language:lint

Use the ``ci:`` prefix for their check-only variants. Coverage scripts are
``test:coverage:unit`` and ``test:coverage:functional``; they need an enabled
coverage driver. ``test:coverage`` runs both.

Version matrix
==============

CI tests supported TYPO3/PHP combinations. To test the other TYPO3 version
locally, prefer a separate checkout next to the first, with a distinct DDEV name
in its untracked :file:`.ddev/config.local.yaml`. Do not run two copies with the
same DDEV project name.

Inside that disposable checkout, a pinned update to 13.4 is:

..  code-block:: bash

    ddev composer update --with-all-dependencies --with=typo3/cms-core:^13.4
    ddev init-typo3
    ddev restart
    ddev composer test
    ddev composer ci:sca

Use ``--with=typo3/cms-core:^14.3`` for 14.3. Reinitialization rebuilds the local
schema, so it destroys demo edits. Composer lock files are not committed here.

Documentation
=============

RST sources live in :file:`Documentation/`, configured by
:file:`Documentation/guides.xml`. From the repository root:

..  code-block:: bash

    docker run --rm -v "$(pwd)":/project \
      ghcr.io/typo3-documentation/render-guides:latest \
      --config=Documentation --no-progress --fail-on-log

Open :file:`Documentation-GENERATED-temp/Index.html` to inspect the output.
The documentation workflow runs the same warning-as-failure render check.
Keep screenshots in :file:`Documentation/Images/` and reuse those paths in the
README; do not create a second screenshot collection.

..  _custom-presets:

Custom installation presets
===========================

Implement ``Xima\XimaTypo3Manual\Generator\Preset\PresetInterface`` in a
container service. It requires these methods:

..  code-block:: php

    public function getIdentifier(): string;
    public function getTitle(): string;
    public function getDescription(): string;
    public function getIconIdentifier(): string;
    public function getData(int $pid): array;

``getData()`` returns a DataHandler datamap, not records written directly to the
database. Its manual root must have placeholder key ``NEW1``, doktype ``701``
and ``is_siteroot = 1``. Use the supplied ``$pid`` as the root's insertion
position. The generator resolves ``NEW1``, creates a site and attaches
``xima/manual``. The shipped ``EmptyManualPreset`` is the minimal reference.

Register an implementation from another extension explicitly:

..  code-block:: yaml

    services:
      Vendor\SitePackage\Manual\ProjectPreset:
        autowire: true
        tags: ['xima_typo3_manual.preset']

Contribution workflow
=====================

Keep changes small, add regression tests for behavioural fixes, and run the
suites and analysis before opening a pull request. Include browser screenshots
for visual changes. See the repository's `contribution guide
<https://github.com/xima-media/xima-typo3-manual/blob/main/CONTRIBUTING.md>`__ and
`security policy
<https://github.com/xima-media/xima-typo3-manual/blob/main/SECURITY.md>`__.

Development fixtures, tests, DDEV and quality configuration are excluded from
release packages through :file:`.gitattributes` and :file:`packaging_exclude.php`.
Do not ship generated documentation or local instance files.
