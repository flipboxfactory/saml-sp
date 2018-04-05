---
title: Field Type
permalink: field/
---

# Field Type


The Link [field type][] is very flexible and supports multiple link [link types][].  One can configure one or many 
[link types][] per field.  We provide many first party [link types][], 
but if you have some [plugin development](https://craftcms.com/docs/plugins/introduction) experience you can also [register and use your own](/developers).

By default, the following first party link types are available:
{% assign url = page.url|append:"types" %}
{% include _nav/tier3.html url=url recursive=false %}

### Creating a new field
To get started, review the [Craft CMS field documentation](https://craftcms.com/docs/fields) for understanding field basics.

#### Settings
Coming soon.

#### Input
Coming soon.

#### Rendering
Calling a Link field in your templates will return a link type object.  You can access values on the link type like: 

{% raw %}
```twig 
{% if entry.linkFieldHandle %}
    <a href="{{ entry.linkFieldHandle.getUrl() }}">
       {{ entry.linkFieldHandle.getText() }}
    </a>
{% endif %}
```
{% endraw %}

*Note: the [templating section](/twig) has additional references to rendering options*

[field type]: https://craftcms.com/docs/fields
[link types]: /field/types
[Twig]: http://twig.sensiolabs.org/ "Twig is a modern template engine for PHP"
[Tags]: http://twig.sensiolabs.org/doc/tags/index.html "Twig Tags"
[Filters]: http://twig.sensiolabs.org/doc/filters/index.html "Twig Filters"
