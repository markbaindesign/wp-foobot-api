<?php

/**
 * Plugin Name: Air Quality Data from Foobot
 * Plugin URI: https://foobot.bain.design
 * Description: Call your air quality data via the Foobot API.
 * Author: Bain Design
 * Version: 1.2.1
 * Author URI: http://bain.design
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: aq-data-foobot
 * Plugin Slug: aq-data-foobot
 */

if (!defined('ABSPATH')) {
   die('Invalid request.');
}

global $wpdb;

define('BD0019__DEVICE_DB_VERSION', '1.2');
define('BD0019__SENSOR_DB_VERSION', '1.4');
define('BD0019__API_OPTIONS_NAME', 'baindesign_foobot_api_settings');
define('BD0019__API_OPTIONS', get_option(BD0019__API_OPTIONS_NAME));
define('BD0019__API_KEY_FIELD', 'baindesign_foobot_api_key');
define('BD0019__API_KEY', BD0019__API_OPTIONS[BD0019__API_KEY_FIELD]);
define('BD0019__API_USER_FIELD', 'baindesign_foobot_api_user');
define('BD0019__API_USER', BD0019__API_OPTIONS[BD0019__API_USER_FIELD]);
define('BD0019__SENSOR_DB_TABLE', $wpdb->prefix . 'bd_foobot_sensor_data');
define('BD0019__DEVICE_DB_TABLE', $wpdb->prefix . 'bd_foobot_device_data');

// Includes
	$path = plugin_dir_path(__FILE__);

	include( $path . 'lib/debug.php');
	include( $path . 'includes/database.php');
	include( $path . 'admin/admin.php');
	include( $path . 'includes/shortcodes.php');
	include( $path . 'includes/sensors.php');
	include( $path . 'includes/api.php');

	// Misc
	include( $path . 'includes/helpers.php');



/**
 * Plugin Init
 * ================
 */

function baindesign_foobot_plugin_init()
{

	function bdf_enqueue_styles() {
		wp_enqueue_style( 'bdf-style', plugins_url( 'assets/style.css', __FILE__ ), false );
		wp_enqueue_style( 'bdf-fonts', 'https://fonts.googleapis.com/css?family=Share+Tech+Mono&display=swap', false );
	}
	add_action('wp_enqueue_scripts','bdf_enqueue_styles');

}
add_action('plugins_loaded', 'baindesign_foobot_plugin_init');

/**
 * Activation Hooks
 * ================
 */

// Create database tables on plugin activation
register_activation_hook(__FILE__, 'bd_foobot_create_sensor_table');
register_activation_hook(__FILE__, 'bd_foobot_create_device_table');
