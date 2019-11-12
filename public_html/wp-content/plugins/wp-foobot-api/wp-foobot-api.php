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

function baindesign_foobot_plugin_init()
{

	include(plugin_dir_path(__FILE__) . 'admin/admin.php');
	include(plugin_dir_path(__FILE__) . 'shortcodes.php');

	function bd_foobot_get_api_key()
	{
		$options = get_option('baindesign_foobot_api_settings');
		return $options['baindesign_foobot_api_key'];
	}

	function bd_get_foobot_device()
	{
		$key = bd_foobot_get_api_key();

		// echo '<pre><code>';
		// var_dump( $key );
		// echo '</code></pre>';

		$url = 'https://api.foobot.io/v2/owner/mark@bain.design/device/';
		$args = array(
			'headers' => array(
				'X-API-KEY-TOKEN' => $key
			)
		);

		$request = wp_remote_get( $url, $args );

		// echo '<pre><code>';
		// var_dump( $request );
		// echo '</code></pre>';

		if (is_wp_error($request)) {
			return false; // Bail early
		}

		$body = wp_remote_retrieve_body($request);

		$api_data = json_decode($body);
		return $api_data;
	}

	/**
	 * Get the API data
	 * 
	 * Now that we have the device UUID, we can call for 
	 * the data from the device. 
	 * 
	 */
	function bd_get_foobot_data()
	{
		$key = bd_foobot_get_api_key();
		$uuid = bd_get_foobot_device_uuid();

		// echo '<pre><code>';
		// var_dump( $key );
		// echo '</code></pre>';

		$url = 'https://api.foobot.io/v2/device/' . $uuid . '/datapoint/0/last/0/?' . $key;
		$args = array(
			'headers' => array(
				'X-API-KEY-TOKEN' => $key
			)
		);

		$request = wp_remote_get( $url, $args );

		// echo '<pre><code>';
		// var_dump( $request );
		// echo '</code></pre>';

		if (is_wp_error($request)) {
			return false; // Bail early
		}

		$body = wp_remote_retrieve_body($request);

		$api_data = json_decode($body);
		return $api_data;
	}

	/**
	 * Get device names
	 */
	function bd_get_foobot_device_name()
	{
		$device = bd_get_foobot_device();
		$name = $device[0]->{"name"};
		return $name;
	}

	/** 
	 * Get device UUID
	 */
	function bd_get_foobot_device_uuid()
	{
		$device = bd_get_foobot_device();
		$uuid = $device[0]->{"uuid"};
		return $uuid;
	}

	/** 
	 * Get current temperature
	 */
	function bd_get_temp_now()
	{
		$data = bd_get_foobot_data();

		// Get the temp
		$datapoints = $data->{"datapoints"};
		$datapoint = $datapoints[0];
		$temp = $datapoint[2];

		// Rounding
		$tr = round($temp, 1);

		// Get the units
		$units = $data->{"units"};

		$u = $units[2];

		// Concat output
		$content = $tr.$u;
		
		return $content;

	}

}
add_action('plugins_loaded', 'baindesign_foobot_plugin_init');
