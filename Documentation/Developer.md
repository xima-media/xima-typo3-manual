# Developer

## Development environment

Everything runs inside DDEV:

```bash
ddev start
ddev init-typo3          # empty database, fixtures and site configuration
ddev composer sca        # composer normalize, php-cs-fixer, PHPStan, rector, linters
ddev exec vendor/bin/phpunit -c phpunit.xml.dist
```

To try the extension against the other supported TYPO3 version, put a second
install next to this one instead of switching versions in place:

```bash
git worktree add ../xima-typo3-manual-v13
printf 'name: xima-typo3-manual-v13\n' > ../xima-typo3-manual-v13/.ddev/config.local.yaml
cd ../xima-typo3-manual-v13 && ddev start
ddev composer update --with "typo3/cms-core:^13.4" --with "typo3/cms-rte-ckeditor:^13.4"
ddev init-typo3
```

## Contribute

You want to customize more? Open an issue or create a pull request!

Please have a look at [`CONTRIBUTING.md`](../CONTRIBUTING.md).
