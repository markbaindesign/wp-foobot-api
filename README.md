# Foobot API WordPress Plugin
_Version 1.0.0_

### by Bain Design

Demo site: https://foobot.bain.design

## VVV Setup

Add the following to your VVV `config.yml` 

```
  wp-foobot-api:
    skip_provisioning: false
    description: "A WordPress plugin to display Foobot data"
    repo: https://github.com/markbaindesign/wp-foobot-api.git
    hosts:
      - wp-foobot-api.test
    custom:
      delete_default_plugins: true
      install_plugins:
        - transients-manager
        - query-monitor
      wp_config_constants:
        WP_DEBUG: true
        WP_DEBUG_LOG: true
        WP_DEBUG_DISPLAY: false
        WP_DISABLE_FATAL_ERROR_HANDLER: true
```

## WordPress Setup

* Log into WordPress and activate the plugin. 
* Once the plugin is activated, go to `Settings > Discussion` and add your Foobot API username and API key.
* Use the shortcode `[foobot-show-data device="foo"]`, where "foo" is your device name, to show the world your AQ data!

## Testing

WP CLI is your friend. Here are some commands to make testing a breeze.

```
wp db tables --all-tables
wp db query 'SELECT * FROM wp_bd_foobot_device_data'
wp db query 'SELECT * FROM wp_bd_foobot_device_data ORDER BY id DESC LIMIT 10'
wp db query 'SELECT * FROM wp_bd_foobot_sensor_data'
```

### Changes to table structure

When changing the database table, you must also:

1. Update the database version function
2. Deactivate the plugin
3. Reactivate the plugin

...in order for the changes to take effect.