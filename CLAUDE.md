# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A WordPress plugin ("Air Quality Data from Foobot", slug `aq-data-foobot`) that pulls air quality readings from the Foobot API and displays them via a shortcode. Published to WordPress.org; this repo is the dev/site scaffold plus the plugin source. Demo site: https://foobot.bain.design.

The actual plugin code lives at `public_html/wp-content/plugins/aq-data-foobot/` — the rest of the repo (`public_html/`, `.ddev/`) is just a local WordPress install used to develop and test it. WordPress core and other dev-tool plugins under `public_html/wp-content` are not tracked in git.

## Local environment

DDEV-based (`.ddev/config.yaml` ships in the repo):

```
ddev start
```

Site: https://wp-foobot-api.ddev.site

```
ddev import-db --file=/path/to/dump.sql
ddev export-db --file=/path/to/dump.sql
```

## Testing and linting

Everything below assumes `cd public_html/wp-content/plugins/aq-data-foobot` first, and runs inside DDEV (dependencies and the WP test library are gitignored, install once per environment):

```
ddev exec "cd public_html/wp-content/plugins/aq-data-foobot && composer install"
ddev exec "cd public_html/wp-content/plugins/aq-data-foobot && bash bin/install-wp-tests.sh aqfoobot_test root root db latest"
```

Run the suite:

```
ddev exec "cd public_html/wp-content/plugins/aq-data-foobot && WP_TESTS_DIR=/tmp/wordpress-tests-lib vendor/bin/phpunit"
ddev exec "cd public_html/wp-content/plugins/aq-data-foobot && vendor/bin/phpcs"
```

To run a single test, pass a filter through to phpunit, e.g. `vendor/bin/phpunit --filter test_method_name`.

`vendor/bin/phpcbf` auto-fixes most PHPCS violations. PHPCS is currently `continue-on-error` in CI — the plugin carries ~69 pre-existing WordPress Coding Standards errors (output escaping, Yoda conditions, i18n translator comments, variable naming) that predate the pipeline and need real code changes, not just reformatting. PHPUnit is the hard gate; don't add code that makes the PHPCS backlog worse, and tighten the CI gate back to blocking once that backlog is cleared.

## Manual DB inspection

Two custom tables (`wp_bd_foobot_device_data`, `wp_bd_foobot_sensor_data`) cache API responses to stay under Foobot's rate limits:

```
ddev wp db query 'SELECT * FROM wp_bd_foobot_device_data'
ddev wp db query 'SELECT * FROM wp_bd_foobot_sensor_data ORDER BY id DESC LIMIT 10'
ddev wp option get baindesign_foobot_api_settings
```

If you change either table's schema, you must also bump the relevant DB version constant (`BD0019__DEVICE_DB_VERSION` / `BD0019__SENSOR_DB_VERSION` in `aq-data-foobot.php`) and deactivate/reactivate the plugin — `dbDelta()` only applies changes on the activation hook.

## Architecture

Everything is procedural, WordPress-plugin style (no classes/namespaces), loaded in this order from `aq-data-foobot.php`:

1. **`lib/debug.php`** — debug helper, gated by the `BD0019__DEBUG` constant (0/1) defined in the main plugin file.
2. **`includes/database.php`** — creates/queries the two custom tables. `bd_foobot_fetch_db_sensors()` / `bd_foobot_fetch_db_devices()` are the only sanctioned read path for the rest of the plugin — they trigger a possible cache-refresh (`bd_foobot_update_*_data()`) before querying, so API calls stay rate-limited.
3. **`admin/admin.php`** — Settings > Foobot API screen; registers the `baindesign_foobot_api_settings` option (API key + user) via the Settings API.
4. **`includes/shortcodes.php`** — registers `[foobot-show-data device="..."]`, which calls into `sensors.php`.
5. **`includes/sensors.php`** — resolves a device name to UUID, formats a sensor reading into the front-end HTML block.
6. **`includes/api.php`** — the only code that talks to `api.foobot.io` directly. Two live-call functions (`bd_foobot_call_api_devices`, `bd_foobot_call_api_sensors`) are wrapped by transient-gated counterparts (`bd_foobot_call_api_trans_devices`, `bd_foobot_call_api_trans_sensors`) that no-op if a transient is still fresh (24h for devices, 10 min for sensors). **Never call the non-transient `bd_foobot_call_api_*` functions directly** — always go through the transient wrappers, and never bypass `database.php`'s fetch functions to hit the API layer from front-end code.
7. **`includes/helpers.php`** — device-name↔UUID lookups, UTC→WP-local timestamp conversion.

Naming/prefix conventions PHPCS enforces (`.phpcs.xml.dist`): all globals must be prefixed with one of `bd_foobot`, `bd_get_foobot`, `bd324`, `baindesign_foobot`, `baindesign_settings_link`, `BD0019`, or `_manually_load_plugin`. `BD0019` is the constant prefix (arbitrary plugin ID), `bd324`/`bd_foobot` are legacy/current function prefixes — match whichever an existing neighboring function in the same file uses.

Debug logging throughout is gated behind `if ( BD0019__DEBUG === 1 )` rather than a real logger — follow this pattern rather than introducing a new one.

## CI/CD

- `.github/workflows/test.yml` — PHPUnit (matrix: PHP 8.1/8.2/8.3) + PHPCS on every push/PR to `master`/`develop`. Public repo, so Actions minutes are free.
- `.github/workflows/deploy.yml` — triggered by pushing a `v*` tag. Re-runs tests, then publishes to the WordPress.org SVN repo and attaches a zip to the matching GitHub Release. Requires `SVN_USERNAME`/`SVN_PASSWORD` repo secrets, **not yet configured** — the deploy step will fail until they're set:
  ```
  gh secret set SVN_USERNAME -R markbaindesign/wp-foobot-api
  gh secret set SVN_PASSWORD -R markbaindesign/wp-foobot-api
  ```
  Until then, WordPress.org releases are a manual `svn` commit from the `svn/` working copy in this repo.
- The release zip is built via `rsync --exclude-from=.distignore` from `public_html/wp-content/plugins/aq-data-foobot/`, not the whole repo.
