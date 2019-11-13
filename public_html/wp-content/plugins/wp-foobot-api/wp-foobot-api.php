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
 * Text Domain: _bd_foobot_data
 * Plugin Slug: wp-foobot-api
 */

include(plugin_dir_path(__FILE__) . 'database.php');
include(plugin_dir_path(__FILE__) . 'admin/admin.php');
include(plugin_dir_path(__FILE__) . 'shortcodes.php');

function baindesign_foobot_plugin_init()
{



	function bd_foobot_get_api_key()
	{
		$options = get_option('baindesign_foobot_api_settings');
		return $options['baindesign_foobot_api_key'];
	}

	/**
	 * This function should not be called directly!!!
	 * ==============================================
	 * 
	 * Using our API key, we question the API to get the UUID of the 
	 * Foobot device. With this info, we can go on to call sensor data. 
	 * 
	 * We must never call this function directly! Instead, we must
	 * retrieve the data we have stored in our custom database table (see 
	 * "database.php"). This function is only used to update the 
	 * table. 
	 * 
	 */
	function bd_get_foobot_device()
	{
		$key = bd_foobot_get_api_key();

		$url = 'https://api.foobot.io/v2/owner/mark@bain.design/device/';
		$args = array(
			'headers' => array(
				'X-API-KEY-TOKEN' => $key
			)
		);

		$request = wp_remote_get($url, $args);

		if (is_wp_error($request)) {
			return false; // Bail early
		}

		$body = wp_remote_retrieve_body($request);

		$api_data = json_decode($body);
		return $api_data;
	}

	/**
	 * This function should not be called directly!!!
	 * ==============================================
	 * 
	 * Get the API data
	 * 
	 * Now that we have the device UUID, we can call for 
	 * the data from the device.
	 * 
	 * We must never call this function directly! Instead, we must
	 * retrieve the data we have stored in our custom database table (see 
	 * "database.php"). This function is only used to update the 
	 * table.
	 * 
	 */
	function bd_get_foobot_data()
	{
		$key = bd_foobot_get_api_key();
		$uuid = bd_get_foobot_device_uuid();

		$url = 'https://api.foobot.io/v2/device/' . $uuid . '/datapoint/0/last/0/?' . $key;
		$args = array(
			'headers' => array(
				'X-API-KEY-TOKEN' => $key
			)
		);

		$request = wp_remote_get($url, $args);

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
		var_dump($device);
		$uuid = $device[0]->{"uuid"};
		return $uuid;
	}

	/** 
	 * Get current temperature and return it as array
	 */
	function bd_get_temp_now()
	{
		/**
		 * First, we need to check our transient and update the data in the 
		 * custom table if necessary. 
		 */
		bd_update_sensor_data();

		/**
		 * Having done that, we can proceed with questioning the database.
		 */
		
		/**
		 * We're storing the retrieved values in an array to make it easy for 
		 * our shortcode to parse the data.
		 */
		$temp_data = array();
		global $wpdb;

		$table_name = $wpdb->prefix . 'bd_foobot_sensor_data';
		
		// Get the timestamp
   	$time = $wpdb -> get_var( "SELECT time FROM $table_name" );
		if( $time != NULL ){
			$temp_data[] = $time; // Add to array
		}		

		// Get the temp
		$temp = $wpdb -> get_var( "SELECT datapoint FROM $table_name" );
		if( $temp != NULL ){
			$temp_data[] = $temp; // Add to array
		}

		// Get the units
		$temp_units = $wpdb -> get_var( "SELECT unit FROM $table_name" );
		if( $temp_units != NULL ){
			$temp_data[] = $temp_units; // Add to array
		}

		return $temp_data;
	}

	/**
	 * Update the sensor readings
	 * ==========================
	 * 
	 * In order to avoid hitting the API to often, using up bandwidth, 
	 * and impacting performance, we store the readings in the database, 
	 * and update them every 5 minutes via an API call.
	 * 
	 * To do this, we store a transient, and check for it each time we
	 * make a call. If it exists, we read the data we have stored in the
	 * custom database table. Otherwise, we make a new call, update the stored
	 * data, and set a new transient.
	 * 
	 * This function should be run before checking the database for data.
	 * 
	 * */

	function bd_update_sensor_data()
	{
		global $wpdb;

		// If an API call has been made within the last 5 mins, 
		// return.
		if (1 == get_transient('foobot-api-data-updated')) {
			// Debug
			error_log("No Foobot API call made at this time.", 0);

			return;
		}

		/**
		 * 
		 * Update the custom database table with fresh data via an API 
		 * 
		 */

		// Get info on devices attached to user account
		$device_data = bd_get_foobot_device();

		/**
		 * Add the retrieved device data to the database
		 */
		// $uuid = $device_data[0]->{"uuid"};
		$device = 'BainBot';
		$sensor = 'tmp';
		
		$sensor_data = bd_get_foobot_data();

		/**
		 * Temperature
		 * ===========
		 */

		// Get timestamp
		$time = $sensor_data->{"start"};

		// Get the temperature
		$datapoints = $sensor_data->{"datapoints"};
		$datapoint = $datapoints[0];
		$temp = $datapoint[2];

		// Get the temperature units
		$units = $sensor_data->{"units"};
		$temp_units = $units[2];
	
		$table_name = $wpdb->prefix . 'bd_foobot_sensor_data';
	
		$wpdb->insert(
			$table_name,
			array(
				'time' 	=> $time,
				'device' => $device,
				'sensor' => $sensor,
				'datapoint' => $temp,
				'unit' 	=> $temp_units,
			)
		);

		// echo $wpdb->show_errors();

		// Transient is set for 5 mins
		set_transient('foobot-api-data-updated', 1, (60 * 5));

		// Debug
		error_log("Foobot sensor data has been updated! Next update > 5 mins.", 0);
	}
	add_action('init', 'bd_update_sensor_data');
}
add_action('plugins_loaded', 'baindesign_foobot_plugin_init');

// Create database table on plugin activation
register_activation_hook(__FILE__, 'bd_foobot_create_table');

// Add data on plugin activation
register_activation_hook(__FILE__, 'bd_foobot_install_data');
