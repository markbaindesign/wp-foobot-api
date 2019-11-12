<?php
/**
 * Plugin Name: Foobot API plugin
 * Plugin URI: https://bain.design/wp-foobot-api
 * Description: Call your air quality data via the Foobot API.
 * Author: Bain Design
 * Version: 0.0.0
 * Author URI: http://bain.design
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: _bd_foobot_api
 * Plugin Slug: wp-foobot-api
 */

function baindesign_foobot_plugin_init() {

	include( plugin_dir_path( __FILE__ ) . 'admin/admin.php');

}
add_action( 'plugins_loaded', 'baindesign_foobot_plugin_init' );