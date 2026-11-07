# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/flipboxfactory/saml-sp).


## Pull Requests

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer).

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the README and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We follow SemVer, and the pull request title picks the version (see [below](#pull-request-titles-and-releases)). Randomly breaking public APIs is not an option.

- **Create topic branches** - Don't ask us to pull from your master branch.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.

- **Ensure tests pass!** - Please run the tests (see below) before submitting your pull request, and make sure they pass. We won't accept a patch until all tests pass.

- **Ensure no coding standards violations** - Please run PHP Code Sniffer using the PSR-2 standard (see below) before submitting your pull request. A violation will cause the build to fail, so please make sure there are no violations. We can't accept a patch if the build fails.


## Pull request titles and releases

Releases are automatic, and the **pull request title** decides what gets released. Write it as a [Conventional Commit](https://www.conventionalcommits.org/en/v1.0.0/): `<type>(<optional scope>): <description>`. Pull requests are squash-merged, so the title becomes the commit message on `master`.

| Title | Release |
|---|---|
| `fix: ...` or `perf: ...` | patch (5.2.2 → 5.2.3) |
| `feat: ...` | minor (5.2.2 → 5.3.0) |
| `<type>!: ...`, or a `BREAKING CHANGE:` line in the pull request description | major (5.2.2 → 6.0.0) |
| `docs`, `test`, `ci`, `chore`, `build`, `refactor`, `style`, `revert` | none |

Examples: `fix: match users case-insensitively`, `feat(sso): add a requireSsoLogin setting`, `docs: fix the installation steps`.

If your pull request releases, add release notes at the top of `CHANGELOG.md` under an `Unreleased` heading, at the same level as the version headings:

```markdown
## Unreleased

### Fixed
- Describe the change for people upgrading
```

Don't change the version in `composer.json` or add a version heading. When the pull request is merged, CI renames `Unreleased` to the new version and date, tags it and publishes the GitHub release. The **Release check** on your pull request shows which version merging it will release.


## Running Tests

``` bash
$ ./vendor/bin/phpunit
```


## Running PHP Code Sniffer

``` bash
$ make phpcs
```

**Happy coding**!
