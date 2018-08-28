# Organization

You may access the following public properties and methods on an [Organization Element].

## Public Properties
All of the standard [Element](https://docs.craftcms.com/api/v3/craft-base-element.html#public-properties) public properties are available plus the following:

| Property              | Type                                  | Description
| --------------------- | ------------------------------------- | ---------------------------------------------------------------------------------
| `state`               | [string], [null]                       | The organization's state (custom defined)
| `dateJoined`          | [DateTime], [null]                     | The date the organization joined

## Public Methods
All of the standard [Element](https://docs.craftcms.com/api/v3/craft-base-element.html#public-methods) public methods are available plus the following:

[[toc]]

### `getUsers( $criteria = [] )` 

Returns: [User Query]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | [User Query] criteria

::: code
```twig
{# Get an Organization Type #}
{% set element = craft.organizations.elements.find('flipbox') %}
{% set users = element.getUsers({status: null}).all() %}
<ul>
{% for user in users %}
    <li>{{ user.id }} - {{ user.getFullName() }}</li>
{% endfor %}
</ul>
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getOrganizations()->get([
    'slug' => 'flipbox'
]);

$users = $element->getUsers([
    'status' => null
]);

foreach ($users as $user) {
    // $user is a /craft/elements/User
}
```
:::


### `getTypes( $criteria = [] )`

Returns: [Organization Type Query]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | [Organization Type Query] criteria

::: code
```twig
{# Get an Organization Type #}
{% set element = craft.organizations.elements.find('flipbox') %}
{% set users = element.getTypes({status: null}).all() %}
<ul>
{% for type in types %}
    <li>{{ type.id }} - {{ type.name }}</li>
{% endfor %}
</ul>
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getOrganizations()->get([
    'slug' => 'flipbox'
]);

$types = $element->getTypes([
    'status' => null
]);

foreach ($types as $type) {
    // $type is a /flipbox/organizations/records/OrganizationType
}
```
:::

### `getType( $identifier )`

Returns: [Organization Type], [null]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$identifier`     | [string], [integer], [null] | [Organization Type] criteria

::: code
```twig
{# Get an Organization Type #}
{% set element = craft.organizations.elements.find('flipbox') %}
{% set type = element.getType('technology') %}
<p>{{ type.id }} - <strong>{{ type.name }}</strong></p>
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getOrganizations()->get([
    'slug' => 'flipbox'
]);

/** @var /flipbox/organizations/records/OrganizationType $type */
$type = $element->getType('technology');
```
:::


### `getPrimaryType()`

Returns: [Organization Type], [null]

::: code
```twig
{# Get an Organization Type #}
{% set element = craft.organizations.elements.find('flipbox') %}
{% set type = element.getPrimaryType() %}
<p>{{ type.id }} - <strong>{{ type.name }}</strong></p>
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getOrganizations()->get([
    'slug' => 'flipbox'
]);

/** @var /flipbox/organizations/records/OrganizationType $type */
$type = $element->getPrimaryType();
```
:::

### `getActiveType()`

Returns: [Organization Type], [null]

::: code
```twig
{# Get an Organization Type #}
{% set element = craft.organizations.elements.find('flipbox') %}
{% set type = element.getActiveType() %}
<p>{{ type.id }} - <strong>{{ type.name }}</strong></p>
```

```php
use flipbox\organizations\Organizations;

$element = Organizations::getInstance()->getOrganizations()->get([
    'slug' => 'flipbox'
]);

/** @var /flipbox/organizations/records/OrganizationType $type */
$type = $element->getActiveType();
```
:::

[integer]: http://www.php.net/language.types.integer
[array]: http://www.php.net/language.types.array
[string]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null
[DateTime]: http://php.net/manual/en/class.datetime.php


[User]: https://docs.craftcms.com/api/v3/craft-elements-user.html
[User Query]: https://docs.craftcms.com/api/v3/craft-elements-db-userquery.html
[Organization Type Query]: /queries/organization-type

[Organization Type]: organization-type.md
[Organization Element]: organization.md