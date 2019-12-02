<?php

/**
 * Plugin Name: Foobot API plugin
 * Plugin URI: https://bain.design/wp-foobot-api
 * Description: Call your air quality data via the Foobot API.
 * Author: Bain Design
 * Version: 1.0.0
 * Author URI: http://bain.design
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: _bd_foobot_data
 * Plugin Slug: wp-foobot-api
 */

// Includes
	$path = plugin_dir_path(__FILE__);
	include($path . 'tests/tests.php');
	include($path . 'includes/database.php');
	include($path . 'admin/admin.php');
	include($path . 'includes/shortcodes.php');
	include($path . 'includes/sensors.php');
	include($path . 'includes/api.php');
	include($path . 'includes/helpers.php');

/**
 * Plugin Init
 * ================
 */

function baindesign_foobot_plugin_init()
{

	// Actions
	add_action('init', 'bd_foobot_update_sensor_data');
	add_action('init', 'bd_foobot_update_device_data');

}
add_action('plugins_loaded', 'baindesign_foobot_plugin_init');

/**
 * Activation Hooks
 * ================
 */

// Create database tables on plugin activation
register_activation_hook(__FILE__, 'bd_foobot_create_sensor_table');
register_activation_hook(__FILE__, 'bd_foobot_create_device_table');
