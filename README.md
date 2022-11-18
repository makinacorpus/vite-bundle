# Vite integration

Helps with Vite generated app Twig integration, no less, no more ("Vite fait, bien fait").

Basic usage:

 * install it,
 * create one or many vite applications, or a single one with multiple
   entry points (really, your architecture or methodology doesn't matter),
 * copy or set the output build path for each app each in its own directory
   under the Symfony `public/` directory,
 * register each application `manifest.json` file in this bundle configuration,
 * use `{{ vite_asset('app_name', 'file_name) }}` where you need it.

And that's it. Twig functions will only return a URL string, it's your job
to pass options to whatever tag you embedded it. Your application, your
rules.

Packages does not specify and Symfony dependency or version constraint. It
should in theory work with any 5.0 version and above. Untested yet with
Symfony 6.x that adds returns types all over the place.

# Roadmap

 - Suport dev server, allowing configuring host name, port and base path
   on a per-app basis.
 - Allow changing the hardcoded `dev` mode being set only when environement
   is `dev` by configuration.
 - That's pretty much what we need I guess.
 - Test it with Symfony 6.x.

# Setup

## Install it

Simply:

```sh
composer require makinacorpus/vite-bundle
```

Then add into your `config/bundles.php` file:

```php
<?php

return [
    // ... your other bundles.
    MakinaCorpus\ViteBundle\ViteBundle::class => ['all' => true],
];
```

There is no specific configuration aside Vite apps registration as documented
below. Every step is important so please take time to read.

## Building your Vite application

We are going to consider that you already have one, and it's outside of the
Symfony project root.

You need to set a few options into your `vite.config.js` file:

```js
import { defineConfig } from 'vite';

// https://vitejs.dev/config/
export default defineConfig({
  // ... your configuraiton.
  build: {
    emptyOutDir: true,
    manifest: true,
    outDir: "../app/public/some-vite-app",
    // The rest is up to you.
  },
  rollupOptions: {
    input: 'src/main.ts',
  },
})
```

The most important detail here is that this bundle requires that you build
a `manifest.json` file, otherwise it'll be unable to find files to include.

If you need to include the `main.ts` file generated asset, you need it to
be specified in `rollupOptions.input` option, otherwise it won't appear in
the manifest file.

Then build it with whatever `npm` or `yarn` tool you are used to:

```sh
npm run build
yarn build
# ...
```

## Register it in the bundle

Create the `config/packages/vite.yaml` file:

```yaml
vite:
    app:
        # Using an absolute path.
        some-vite-app:
            manifest: "%kernel.project_dir%/public/some-vite-app/manifest.json"

        # Using a "public/" relative path.
        some-vite-app:
            manifest: "some-vite-app/manifest.json"
```

Beware we require that it must be under the `public/` directory.

## Put some assets anywhere you need it

Edit any Twig file:

```twig
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<link rel="stylesheet" href="{{ vite_asset('some-vite-app', 'styles.css') }}"/>
<script crossorigin type="module" src="{{ vite_asset('some-vite-app', 'index.js') }}"></script>
<script type="module">
  {# Any code that uses code from your built module needs to be declared
     as a module itself, because modules are loaded way further in time
     than common scripts. #}
  do_something_in_vite_app();
</script>
</head>
<body>
  <div id="app"></div>
</body>
</html>
```

# A word about manifest.json parsing

When your environment is `dev`, `manifest.json` files are not parsed during
container compilation, but at runtime when first accessed. This means that
you don't need any further operation when rebuilding your Vite app.

For all other environements, `manifest.json` files are parsed during container
compilation, entries are cached into the container itself: you will need to
rebuild caches when you redeploy your app.

If you need to be able to configure environment or need a different behavior,
please open an issue, any improvement or suggestion is welcome.

# Dev mode

... is sadly not supported yet, but it will be soon.

# Alternatives

A few alternatives exist, at least those:

 - https://packagist.org/packages/bechir/vite-bundle
 - https://packagist.org/packages/daddl3/vite-symfony-bundle
 - https://packagist.org/packages/pentatrion/vite-bundle

All three have taken a different paths. Biggest different with this package
is we do not attempt to replicate `symfony/webpack-encore-bundle` on its own.
We consider that if you are interested in Vite and willing to use it, simply
create a vanilla Vite project: this bundle will only give you a way to
easily import its assets into Twig templates.
