# Air Quality Data from Foobot WordPress Plugin

_Version 1.2.1_

### by Bain Design

Demo site: https://foobot.bain.design

# WordPress Setup

If you just want to install the plugin and display your air quality data on your website, here's what you need to do.

* Download the plugin .zip archive and unpack it. 
* Locate the plugin file and upload to your website Plugins folder.
* Log into WordPress and activate the plugin. 
* Once the plugin is activated, go to `Settings > Foobot API` and add your Foobot API username and API key ([Get your Foobot API key here](https://api.foobot.io/apidoc/index.html "Foobot API")).
* Use the shortcode `[foobot-show-data device="foo"]`, where `foo` is your device name, to show the world your air quality data!

# Development

If you want to collaborate on this plugin or fork your own version, here's a brief guide.

# DDEV setup

This plugin is developed locally with [DDEV](https://ddev.com/). The project ships a `.ddev/config.yaml`, so setup is:

```
ddev start
```

The site is then available at:

- https://wp-foobot-api.ddev.site

WordPress core and the dev-tool plugins (`classic-editor`, `debug-bar`, `transients-manager`) live under `public_html/wp-content` alongside the plugin itself and are not tracked in this repo's git history.

To import a database dump:

```
ddev import-db --file=/path/to/dump.sql
```

To export the current database:

```
ddev export-db --file=/path/to/dump.sql
```

# Manual testing

WP CLI is your friend (via `ddev wp ...` or `ddev ssh` then `wp ...`). Here are some commands to make testing a breeze.

```
ddev wp db tables --all-tables
ddev wp db query 'SELECT * FROM wp_bd_foobot_device_data'
ddev wp db query 'SELECT * FROM wp_bd_foobot_device_data ORDER BY id DESC LIMIT 10'
ddev wp db query 'SELECT * FROM wp_bd_foobot_sensor_data'
ddev wp option get baindesign_foobot_api_settings
```

## Changes to table structure

When changing the database table, you must also:

1. Update the database version function
2. Deactivate the plugin
3. Reactivate the plugin

...in order for the changes to take effect.

# Automated testing

The plugin has a PHPUnit test suite plus WordPress Coding Standards linting, both runnable inside DDEV. Dependencies and the WordPress test library aren't tracked in git, so set them up once per environment:

```
cd public_html/wp-content/plugins/aq-data-foobot
ddev exec "cd public_html/wp-content/plugins/aq-data-foobot && composer install"
ddev exec "cd public_html/wp-content/plugins/aq-data-foobot && bash bin/install-wp-tests.sh aqfoobot_test root root db latest"
```

Then, from `public_html/wp-content/plugins/aq-data-foobot`:

```
ddev exec "cd public_html/wp-content/plugins/aq-data-foobot && WP_TESTS_DIR=/tmp/wordpress-tests-lib vendor/bin/phpunit"
ddev exec "cd public_html/wp-content/plugins/aq-data-foobot && vendor/bin/phpcs"
```

(`vendor/bin/phpcbf` will auto-fix most PHPCS formatting violations.)

# CI/CD

GitHub Actions (`.github/workflows/`) runs PHPUnit + PHPCS checks on every push/PR to `master` and `develop`. This repo is public, so Actions minutes are free (no billing risk).

The PHPCS job is currently non-blocking (`continue-on-error: true`) — this plugin has ~69 pre-existing WordPress Coding Standards errors (output escaping, Yoda conditions, i18n translator comments, variable naming) that predate this pipeline and need real code changes, not just reformatting, to fix. PHPUnit is the hard gate. Tighten `lint` back to blocking once that backlog is cleared.

Pushing a `v*` tag (e.g. `v1.3.0`) triggers `deploy.yml`, which re-runs the test suite and, if it passes, publishes the plugin to the WordPress.org SVN repository and attaches a zip to the matching GitHub Release. This requires `SVN_USERNAME` and `SVN_PASSWORD` to be set as repository secrets — **not yet configured**, so `deploy.yml` will fail at the deploy step until they're added:

```
gh secret set SVN_USERNAME -R markbaindesign/wp-foobot-api
gh secret set SVN_PASSWORD -R markbaindesign/wp-foobot-api
```

Until then, releases to WordPress.org remain a manual `svn` commit from the `svn/` working copy in this repo, same as before.