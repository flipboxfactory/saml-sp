# Organization Type Site Settings

You may access the following public properties and methods on an [Organization Type Site Settings].

[Organization Type Site Settings]: organization-type-site-settings "Organization Type Site Settings"

## Public Properties
The following properties are available:

| Property              | Type                                  | Description
| --------------------- | ------------------------------------- | ---------------------------------------------------------------------------------
| `typeId`              | [integer]                             | The organization's state (custom defined)
| `siteId`              | [integer]                             | The date the organization joined
| `hasUrls`             | [boolean]                             | The date the organization joined
| `uriFormat`           | [string], [null]                      | The date the organization joined
| `template`            | [string], [null]                      | The date the organization joined


## Public Methods
The following methods are available:

### `getSite()`

Returns: [Site]

::: code
```twig
{# Get an Organization Type #}
{% set object = craft.organizations.organizationTypes.get('technology') %}
{% set siteSettings = object.getSiteSettings() %}
<ul>
{% for settings in siteSettings %}
    {% set site = settings.getSite() %}
    <li>{{ site.id }} - {{ site.name }}</li>
{% endfor %}
</ul>
```

```php
use flipbox\organizations\Organizations;

$object = Organizations::getInstance()->getOrganizationTypes()->get([
    'handle' => 'technology'
]);

$siteSettings = $object->getSiteSettings();

foreach ($siteSettings as $settings) {
    $site = $settings->getSite();
}
```
:::


### `getType()`

Returns: [Organization Type]

::: code
```twig
{# Get an Organization Type #}
{% set object = craft.organizations.organizationTypes.get('technology') %}
{% set siteSettings = object.getSiteSettings() %}
<ul>
{% for settings in siteSettings %}
    {% set type = settings.getType() %}
    <li>{{ type.id }} - {{ type.handle }}</li>
{% endfor %}
</ul>
```

```php
use flipbox\organizations\Organizations;

$object = Organizations::getInstance()->getOrganizationTypes()->get([
    'handle' => 'technology'
]);

$siteSettings = $object->getSiteSettings();

foreach ($siteSettings as $settings) {
    $type = $settings->getType();
}
```
:::



### `hasUrls()`

Returns: [boolean]

::: code
```twig
{# Get an Organization Type #}
{% set object = craft.organizations.organizationTypes.get('technology') %}
{% set siteSettings = object.getSiteSettings() %}
<ul>
{% for settings in siteSettings %}
    <li>
        {% if settings.hasUrls %}
            {{ settings.uriFormat }} - {{ settings.template }}
        {% else %}
            No Url
        {% endif %}
    </li>
{% endfor %}
</ul>
```

```php
use flipbox\organizations\Organizations;

$object = Organizations::getInstance()->getOrganizationTypes()->get([
    'handle' => 'technology'
]);

$siteSettings = $object->getSiteSettings();

foreach ($siteSettings as $settings) {
    if ($settings->hasUrls()) {
        // Do something w/ url
    } else {
        // Do something w/ out url    
    }
}
```
:::

[integer]: http://www.php.net/language.types.integer
[boolean]: http://www.php.net/language.types.boolean
[string]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null
[array]: http://www.php.net/language.types.array

[Site]: https://docs.craftcms.com/api/v3/craft-models-site.html
[Organization Type]: organization-type.md
