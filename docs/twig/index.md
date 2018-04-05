---
layout: default
title: Twig
permalink: twig/
---

# Twig

When rending a Link field, you are actually interacting with one of many [Link Types][] that implement a common interface.  Therefore, 
each Link Type can provide additional values, but all will have the following:

{% include _nav/tier3.html url=page.url recursive=false removeFirst=true %}

---

### `getUrl()`
{% raw %}
```twig
{{ entry.linkFieldHandle.getUrl() }}
```
{% endraw %}

### `getText()`
{% raw %}
```twig
{{ entry.linkFieldHandle.getText() }}
```
{% endraw %}

### `getHtml($attributes = [])`
{% raw %}
```twig
{{ entry.linkFieldHandle.getUrl({
    'class': 'foo',
    'data-node': 'bar'
}) }}
```
{% endraw %}


[Twig]: http://twig.sensiolabs.org/ "Twig is a modern template engine for PHP"
[Tags]: http://twig.sensiolabs.org/doc/tags/index.html "Twig Tags"
[Filters]: http://twig.sensiolabs.org/doc/filters/index.html "Twig Filters"
