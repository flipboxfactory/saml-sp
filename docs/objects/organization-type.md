# Organization Type

You may access the following public properties and methods on an [Organization Type].

## Public Properties
The following properties are available:

| Property              | Type                                  | Description
| --------------------- | ------------------------------------- | ---------------------------------------------------------------------------------
| `id`                  | [integer]                             | The organization type's Id
| `handle`              | [string]                              | The organization type's reference name
| `name`                | [string]                              | The organization type's human readable name
| `fieldLayoutId`       | [integer], [null]                     | The organization type's layout Id
| `uid`                 | [string], [null]                      | The universally unique identifier
| `dateCreated`         | [DateTime], [null]                    | The date the organization type was created
| `dateUpdated`         | [DateTime], [null]                    | The date the organization type was last updated


## Public Methods
The following methods are available:

### `getFieldLayout()`

Returns: [Field Layout]

::: code
```twig
{# Get an Organization Type #}
{% set object = craft.organizations.organizationTypes.get('technology') %}
{% set fieldLayout = object.getFieldLayout() %}
<p>{{ fieldLayout.id }}</p>
```

```php
use flipbox\organizations\Organizations;

$object = Organizations::getInstance()->getOrganizationTypes()->get([
    'handle' => 'technology'
]);

$fieldLayout = $object->getFieldLayout();
```
:::



### `object.getSiteSettings()`

Returns: an array of [Organization Type Site Settings]

::: code
```twig
{# Get an Organization Type #}
{% set object = craft.organizations.organizationTypes.find('technology') %}
{% set siteSettings = object.getSiteSettings() %}
<ul>
{% for site in siteSettings %} 
    <li>{{ site.hasUrls }} - {{ site.getUriFormat() }} - {{ site.getTemplate() }}</li>
{% endfor %}
</ul>
```

```php
use flipbox\organizations\Organizations;

$object = Organizations::getInstance()->getOrganizationTypes()->get([
    'handle' => 'technology'
]);

$siteSettings = $object->getSiteSettings();

foreach ($siteSettings as $site) {
    // $site
}
```
:::

[integer]: http://www.php.net/language.types.integer
[string]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null
[array]: http://www.php.net/language.types.array
[DateTime]: http://php.net/manual/en/class.datetime.php

[Field Layout]: https://docs.craftcms.com/api/v3/craft-models-fieldlayout.html

[Organization Type]: organization-type.md
[Organization Type Site Settings]: organization-type-site-settings.md
