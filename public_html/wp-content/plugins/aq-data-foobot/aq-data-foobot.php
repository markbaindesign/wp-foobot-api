<?php

/**
 * Plugin Name: Air Quality Data from Foobot
 * Plugin URI: https://foobot.bain.design
 * Description: Call your air quality data via the Foobot API.
 * Author: Bain Design
 * Version: 1.0.0
 * Author URI: http://bain.design
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: aq-data-foobot
 * Plugin Slug: aq-data-foobot
 */

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

	// Tests 
	include( $path . 'tests/tests-api.php');
	include( $path . 'tests/tests-data.php');
	include( $path . 'tests/tests-database.php');
	include( $path . 'tests/tests-transients.php');

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
