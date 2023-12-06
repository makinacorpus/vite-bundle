# Vite integration

Helps with Vite generated app Twig integration, no less, no more ("Vite fait, bien fait").

Basic usage:

 * install it,
 * create one or many vite applications, or a single one with multiple
   entry points (really, your architecture or methodology doesn't matter),
 * copy or set the output build path for each app each in its own directory
   under the Symfony `public/` directory,
 * register each application `manifest.json` file in this bundle configuration,
 * use `{{ vite_head('app_name') }}` and `{{ vite_body('app_name') }}` in pages you need it.

Twig functions are opiniated and will include the app as a Javascript module.

If your kernel current environment is `dev`, it will include a link toward
the development server instead. For this to work, you need to have Vite running.

Packages does not specify and Symfony dependency or version constraint. It
should in theory work with any 6.0 and 7.0 version.

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
    rollupOptions: {
      input: 'src/main.ts',
    },
    // The rest is up to you.
  },
})
```

The most important detail here is that this bundle requires that you build
a `manifest.json` file, otherwise it'll be unable to find files to include.

If you need to include the `main.ts` file generated asset, you need it to
be specified in `build.rollupOptions.input` option, otherwise it won't appear
in the manifest file.

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
            dev_url: http://localhost:5173

        # Using a "public/" relative path.
        some-vite-app:
            manifest: "some-vite-app/manifest.json"
            dev_url: http://localhost:5173
```

Beware we require that it must be under the `public/` directory.

The `dev_url` entry allows you to use the development mode when the kernel
environment is `dev`. Right now the environment name is hardcoded but will
be configurable later.

If you explicitely set `null` for `dev_url` then the development mode is
disabled.

## Put some assets anywhere you need it

Edit any Twig file and add the following two function calls:

```twig
<!DOCTYPE html>
<html lang="en">
<head>
<!--
  Your own head.
  -->
{{ vite_head('some-vite-app') }}
</head>
<body>
  {{ vite_body('some-vite-app') }}
</body>
</html>
```

Some other parameters can change the head script behaviour:
 - First parameter is your app name defined in yaml configuration and is
   required.
 - Second parameter is entry point file name, default is set to `src/main.ts`
   i.e. the same as the given name in `vite.config.js` file
   `build.rollupOptions.input` entry. If you gave another name, you must change
   the name here.

# A word about manifest.json parsing

When your environment is `dev`, `manifest.json` files are not parsed during
container compilation, but at runtime when first accessed. This means that
you don't need any further operation when rebuilding your Vite app.

For all other environements, `manifest.json` files are parsed during container
compilation, entries are cached into the container itself: you will need to
rebuild caches when you redeploy your app.

If you need to be able to configure environment or need a different behavior,
please open an issue, any improvement or suggestion is welcome.

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
