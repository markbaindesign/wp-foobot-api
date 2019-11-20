<?php

/**
 * Set database versions
 */

// Device
global $bd_foobot_device_db_version;
$bd_foobot_device_db_version = '1.0';

// Sensors
global $bd_foobot_sensor_db_version;
$bd_foobot_sensor_db_version = '1.0';

/** Get Options
 * ============
 * 
 * Retrieve values stored in WordPress database
 * options table. 
 */

// Get API key from the database
function bd_foobot_get_api_key()
{
   $options = get_option('baindesign_foobot_api_settings');
   return $options['baindesign_foobot_api_key'];
}

// Get API username from the database
function bd_foobot_get_api_user()
{
	// TO DO
   // $options = get_option('baindesign_foobot_api_settings');
	// return $options['baindesign_foobot_api_key'];
	return 'mark@bain.design'; // Temp
}

/**
 * Create database tables
 * ======================
 */

// Create table to store sensor data
function bd_foobot_create_sensor_table()
{
	global $wpdb;
	global $bd_foobot_sensor_db_version;

	$table_name = $wpdb->prefix . 'bd_foobot_sensor_data';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		device tinytext NOT NULL,
		unitTime tinytext NOT NULL,
		datapointTime float NOT NULL,
		unitPm tinytext NULL,
		datapointPm float NULL,
		unitTmp tinytext NULL,
		datapointTmp float NULL,
		unitHum tinytext NULL,
		datapointHum float NULL,
		unitCo2 tinytext NULL,
		datapointCo2 float NULL,
		unitVoc tinytext NULL,
		datapointVoc float NULL,
		unitAllpollu tinytext NULL,
		datapointAllpollu float NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	add_option('bd_foobot_sensor_db_version', $bd_foobot_sensor_db_version);
}

// Create table to store device data
function bd_foobot_create_device_table()
{
	global $wpdb;
	global $bd_foobot_device_db_version;

	$table_name = $wpdb->prefix . 'bd_foobot_device_data';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		device tinytext NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	add_option('bd_foobot_device_db_version',$bd_foobot_device_db_version);
}

/**
 * Fetch data from the database
 * ============================
 */

function bd_foobot_fetch_latest_sensor_data(){

	// To DO
	// Pass the sensor you want to this function
	
	// Vars
	global $wpdb;
	$table_name = $wpdb->prefix . 'bd_foobot_sensor_data';
	
	// $data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table_name}` ORDER BY `id` DESC LIMIT 1", $sensor) );
	$data = $wpdb->get_row( "SELECT * FROM `{$table_name}` ORDER BY `id` DESC LIMIT 1", ARRAY_A );

	return $data;

}

/**
 * Update database data
 * ====================
 * 
 * These functions use transients to avoid hitting the API
 * limit. 
 */

// Update device data
function bd_foobot_update_device_data()
{
   global $wpdb;

   // If an API call has been made within the last 24 hours, 
   // return.
   if (1 == get_transient('foobot-api-device-updated')) {
      // Debug
      error_log("No Foobot Device API call made at this time.", 0);
      return;
   }

   // Get the device data
   $device_data = bd_foobot_call_device_api();

   // Add the API to the database
   $table_name = $wpdb->prefix . 'bd_foobot_device_data';
   foreach ( $device_data as $key => $value ){
      $wpdb->insert( $table_name, array( $key => $value ));
   }

   // Transient is set for 24 hours
   set_transient('foobot-api-device-updated', 1, (60 * 60 * 24));

   // Debug
   error_log("Foobot sensor data has been updated! Next update > 24 hours.", 0);
}

// Update sensor data
function bd_foobot_update_sensor_data()
{
   global $wpdb;

   // If an API call has been made within the last 5 mins, 
   // return.
   if (1 == get_transient('foobot-api-data-updated')) {
      // Debug
      error_log("No Foobot API call made at this time.", 0);

      return;
   }

   // Get the device data
   $sensor_data = bd_foobot_call_data_api();

   // Add the API to the database
   $table_name = $wpdb->prefix . 'bd_foobot_sensor_data';
   foreach ( $sensor_data as $key => $value ){
      $wpdb->insert( $table_name, array( $key => $value ));
   }

   // Temperature
   $unitTmp = $sensor_data->{"unitTmp"};
   $datapointTmp = $sensor_data->{"datapointTmp"};

   // Device
   $device = $sensor_data->{"device"};

   // Humidity
   $unitHum = $sensor_data->{"unitHum"};
   $datapointHum = $sensor_data->{"datapointHum"};

   // CO2
   $unitCo2 = $sensor_data->{"unitCo2"};
   $datapointCo2 = $sensor_data->{"datapointCo2"};

   // Voc
   $unitVoc = $sensor_data->{"unitVoc"};
   $datapointVoc = $sensor_data->{"datapointVoc"};

   // All Pollution
   $unitAllpollu = $sensor_data->{"unitAllpollu"};
   $datapointAllpollu = $sensor_data->{"datapointAllpollu"};

   // Get timestamp
   $timestamp = $sensor_data->{"start"};
   $time = date('Y-m-d H:i:s', $timestamp);

   // Get the temperature
   $datapoints = $sensor_data->{"datapoints"};
   $datapoint = $datapoints[0];
   $datapointTmp = $datapoint[2];

   // Get the temperature units
   $units = $sensor_data->{"units"};
   $unitTmp = $units[2];

   /**
    * Insert data into custom database table
    */


   /**
    * 
   $wpdb->insert(
      $table_name,
      array(
         'time'                  => $time,
         'device'                => $device,
         'unitPm'                => $sensor_data->{"unitPm"},
         'datapointPm'           => $sensor_data->{"datapointPm"},
         'unitTmp'               => $unitTmp,
         'datapointTmp'          => $datapointTmp,
         'unitHum'               => $unitHum,
         'datapointHum'          => $datapointHum,
         'unitCo2'               => $unitCo2,
         'datapointCo2'          => $datapointCo2,
         'unitVoc'               => $unitVoc,
         'datapointVoc'          => $datapointVoc,
         'unitAllpollu'          => $unitAllpollu,
         'datapointAllpollu'     => $datapointAllpollu,
      )
   ); 
   **/

   // echo $wpdb->show_errors();

   // Transient is set for 5 mins
   set_transient('foobot-api-data-updated', 1, (60 * 5));

   // Debug
   error_log("Foobot sensor data has been updated! Next update > 5 mins.", 0);
}