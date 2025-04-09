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

# Setup TYPO3 with a prefilled database
ddev init-typo3
```

You can access the TYPO3 backend at <https://xima-typo3-manual.ddev.site/typo3/>.

To login, you can use the username `admin` and password `Passw0rd!`.

## Code analysis and linters

```bash
# Run a specific linter
ddev composer php:lint
ddev composer typoscript:lint
ddev composer xml:lint
ddev composer yaml:lint
ddev composer php:stan
ddev composer php:fixer
```

## Submit a pull request

Thanks for contributing!

Once you have finished your work, please **submit a pull request** and describe
what you've done. Ideally, your PR references an issue describing the problem
you're trying to solve.

All described code quality tools are automatically executed on each pull request.
