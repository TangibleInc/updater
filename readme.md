# Updater

Plugin and theme updater module

Based on:

- [Plugin update checker](https://github.com/YahnisElsts/plugin-update-checker/)
- [WP Update Server](https://github.com/YahnisElsts/wp-update-server/)

#### Source code

https://github.com/tangibleinc/updater

## Install

Add as dependency in `composer.json` and run `composer update`.

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:tangibleinc/updater"
    }
  ],
  "require": {
    "tangible/updater": "dev-main"
  },
  "minimum-stability": "dev"
}
```

Or install as a module in `tangible.config.js`.

```js
export default {
  install: [
    {
      git: 'git@github.com:tangibleinc/updater',
      dest: 'vendor/tangible/updater',
      branch: 'main',
    },
  ]
}
```

## Use

After loading the updater, its newest version instance is ready on `plugins_loaded` action.

```php
use tangible\updater;

require_once __DIR__ . '/vendor/tangible/updater/index.php';

add_action('plugins_loaded', function() {

  updater\register_plugin([
    'name' => 'example-plugin',
    'file' => __FILE__
  ]);
});
```

Register the plugin with its name and file path.

### Cloud

Optionally set the property `cloud` to pass additional parameters to the update server.

```php
updater\register_plugin([
  'name' => $plugin->name,
  'file' => __FILE__,
  'cloud' => [
    'id' => '',      // Plugin ID (Required)
    'license' => '', // License key
    'api' => '',     // Update API server's URL (Optional)
  ]
]);
```

## Develop

Prerequisites: [Git](https://git-scm.com/), [Node](https://nodejs.org), [Docker](https://docs.docker.com/engine/)

Clone project and install dependencies.

```sh
git clone https://github.com/tangibleinc/updater
cd updater
npm install
```

Start local dev server for WordPress test site using [`wp-env`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env).

```sh
npm run start
```

When running the first time, install Composer dev dependencies for testing.

```sh
npm run composer:install
```

Run tests.

```sh
npm run test
```

Stop the server.

```sh
npm run stop
```

Remove Docker images and volumes for the sites.

```sh
npm run destroy
```

### Customize environment

Create a file named [`.wp-env.override.json`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/#wp-env-override-json) to customize the WordPress environment. This file is listed in `.gitignore` so it's local to your setup.

It's useful for changing the site port numbers or mounting local folders into the virtual file system. For example, to link another plugin in the parent directory:

```json
{
  "mappings": {
    "wp-content/plugins/example-plugin": "../example-plugin"
  }
}
```
