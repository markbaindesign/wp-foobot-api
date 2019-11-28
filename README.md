# Foobot API WordPress Plugin
_Version 1.0.0_

### by Bain Design

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
      install_test_content: true
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
* Once the plugin is activated, go to `Settings > Discussion` and add your Foobot API key.