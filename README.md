# Flarum Pages Generator

This is not a Flarum extension.
This package provides a Flarum extender that you can use in the local `extend.php` to define custom pages.

Install at the root of your Flarum, or as a dependency to one of your custom extensions.

I don't recommend bundling this as a dependency of public extensions.

**This package is in beta! Breaking releases will be released regularly. Make sure to use `^` or `~` semver constraints!**

    composer require clarkwinkelmann/flarum-pages-generator

## Usage

In `extend.php`:

```php

return [
    (new \ClarkWinkelmann\PagesGenerator\Pages())
        ->source(__DIR__ . '/pages')
        ->mithrilComponent('ContactForm', "flarum.extensions['acme-basic-form'].ContactForm"),
];

```

`->source(string $path)` defines a source folder to enumerate for pages.

`->mithrilComponent(string $tag, string $import)` optionally defines a mapping of an HTML tag to a Mithril component.
The expression will be called with `eval()`.

You can call `source` and `mithrilComponent` multiple times on a single extender instance.

**At the moment, a single extender instance must exist in the Flarum application!**
If multiple extensions try to use it, it will break.

## Pages

Pages can be Markdown (`.md`) or HTML (`.html`) and must include a [Yaml front matter](https://github.com/spatie/yaml-front-matter).

Markdown is parsed using the Flarum formatter.
Any markdown or bbcode made available by extensions will be available.

The filename without file extension will be the page URL.
Subfolders under the source directory are preserved as part of the URL.

Example: basic markdown

```
---
title: About us
---

This is a markdown text with **formatting**.
```

Example: basic HTML

```
---
title: Find Us
---

<p>It's very simple to find us</p>

<iframe src=""></iframe>
```

Example: Markdown page with custom component

```
---
title: Contact
---

Use the form to contact us:

<ContactForm title="A title attr"></ContactForm>
```
