<p align="center"><img src="src/icon.svg" width="96" height="96" alt="SAML SSO Service Provider icon"></p>

# SAML SSO Service Provider for Craft CMS

[![CI](https://github.com/flipboxfactory/saml-sp/actions/workflows/ci.yml/badge.svg)](https://github.com/flipboxfactory/saml-sp/actions/workflows/ci.yml)
[![Docs](https://github.com/flipboxfactory/saml-sp/actions/workflows/docs.yml/badge.svg)](https://saml-sp.flipboxfactory.com/)
[![Latest Version](https://img.shields.io/packagist/v/flipboxfactory/saml-sp.svg?style=flat-square)](https://packagist.org/packages/flipboxfactory/saml-sp)
[![Total Downloads](https://img.shields.io/packagist/dt/flipboxfactory/saml-sp.svg?style=flat-square)](https://packagist.org/packages/flipboxfactory/saml-sp)

Single Sign-On (SSO) and Single Logout (SLO) for Craft CMS using SAML 2.0. Your users sign in to Craft through an identity provider such as Okta, Microsoft Entra ID or Google Workspace.

- SSO over HTTP-POST and HTTP-Redirect bindings
- Signed and encrypted assertions
- User attribute and user group sync on login
- Single Logout
- Multiple identity providers
- MySQL and PostgreSQL

**[Read the documentation →](https://saml-sp.flipboxfactory.com/)**

## Requirements

- Craft CMS 5.6+
- PHP 8.2+

## Installation

Install from the Craft Plugin Store, or with Composer:

```bash
composer require flipboxfactory/saml-sp
php craft plugin/install saml-sp
```

Then follow the [quick start](https://saml-sp.flipboxfactory.com/quick-start.html).

## Development

Checks follow Craft's [coding guidelines](https://craftcms.com/docs/5.x/extend/coding-guidelines.html): [craftcms/ecs](https://github.com/craftcms/ecs) and [craftcms/phpstan](https://github.com/craftcms/phpstan). Tests use Codeception with Craft's test module and need a MySQL database:

```bash
docker run -d --rm --name saml-sp-test-mysql -e MYSQL_ROOT_PASSWORD=password \
  -e MYSQL_DATABASE=saml_sp_test -p 127.0.0.1:3306:3306 mysql:8.0
cp tests/.env.example tests/.env   # adjust CRAFT_DB_* if needed

composer check           # check-cs + phpstan + test
composer test-coverage   # needs pcov or Xdebug; CI requires 85% line coverage
composer fix-cs          # auto-fix coding standards
```

CI runs the same checks on every push and pull request. Documentation lives in [`docs/`](docs) and is published from `master`.

## Security

Please report vulnerabilities privately to [support@flipboxfactory.com](mailto:support@flipboxfactory.com) rather than opening a public issue.

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md).

## Credits

- [Flipbox Digital](https://github.com/flipbox)
- [garycrane](https://github.com/garycrane/)

## License

See [LICENSE](LICENSE.md).
