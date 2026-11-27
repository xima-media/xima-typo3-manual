# Contributing

Thanks for considering contributing to this extension!

For development, we use [DDEV](https://ddev.readthedocs.io/en/stable/). Set it up before continuing.

## Preparation

```bash
# Clone repository
git clone git@github.com:xima-media/xima-typo3-manual.git
cd xima-typo3-manual

# Start DDEV project
ddev start

# Install dependencies
ddev composer install

# Setup TYPO3 with a prefilled database (destroys the local DDEV database)
ddev init-typo3
ddev restart
```

Use the URL reported by `ddev describe`, with `/manual/` for the manual and `/typo3/` for the backend. Login: `admin` / `Passw0rd!`. Do not expose the demo publicly with these credentials.

## Code analysis and linters

```bash
# Fix what can be fixed automatically and run all linters
ddev composer sca

# Run all linters in check mode, like the CI
ddev composer ci:sca

# Run a specific linter
ddev composer php:lint
ddev composer php:rector
ddev composer php:fixer
ddev composer php:stan
ddev composer typoscript:lint
ddev composer yaml:lint
ddev composer language:lint
```

## Tests

```bash
# Unit and functional tests
ddev composer test

# A single suite
ddev composer test:unit
ddev composer test:functional
```

## Documentation

RST sources and screenshots live in `Documentation/`. Render them from the repository root:

```bash
docker run --rm -v "$(pwd)":/project \
  ghcr.io/typo3-documentation/render-guides:latest \
  --config=Documentation --no-progress --fail-on-log
```

The [developer guide](https://docs.typo3.org/p/xima/xima-typo3-manual/main/en-us/Developer/Index.html) covers the version matrix and custom presets. Inspect the diff after `composer sca`, especially the generated PHPStan baseline; stage new files before the final `ci:sca` check.

## Submit a pull request

Thanks for contributing!

Once you have finished your work, please **submit a pull request** and describe
what you've done. Ideally, your PR references an issue describing the problem
you're trying to solve.

All described code quality tools are automatically executed on each pull request.
