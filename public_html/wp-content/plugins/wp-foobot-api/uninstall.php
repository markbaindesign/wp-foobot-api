<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
   die;
}

$options_name =     'baindesign_foobot_api_settings';

delete_option($options_name);

// drop a custom database tables
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bd_foobot_sensor_data");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bd_foobot_device_data");
