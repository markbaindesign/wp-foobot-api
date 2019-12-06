<?php

/**
 * Set database versions
 */

// Device
global $bd_foobot_device_db_version;
$bd_foobot_device_db_version = '1.2';

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
   $options = get_option('baindesign_foobot_api_settings');
	return $options['baindesign_foobot_api_user'];
}

/**
 * ======================
 * Create database tables
 * ======================
 */

 /**
 * Create the 2 custom tables needed for this plugin, 
 * one to store the device data, another to store the
 * sensor readings. 
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
		timestamp int NOT NULL,
		name tinytext NOT NULL,
		uuid tinytext NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	add_option('bd_foobot_device_db_version',$bd_foobot_device_db_version);
}

/**
 * ============================
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

function bd_foobot_get_current_devices(){
   
   global $wpdb;
   $wpdb->show_errors();

   // Vars
   $table_name = $wpdb->prefix . 'bd_foobot_device_data';

   // Update the device table if required
   bd_foobot_update_device_data();
   
   // Get all the results
   // TO DO: Only return results from the last 24 hours?

   // Order the results

   // Get the most recent result and return rows that
   // match the same timestamp
	
   //$data = $wpdb->get_row( "SELECT * FROM `{$table_name}` WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)", ARRAY_A );
   $data = array();
	$data = $wpdb->get_row( "SELECT * FROM `{$table_name}` ORDER BY `id` DESC LIMIT 1", ARRAY_A );

   $timestamp = $data["timestamp"];

   //$latest = array();
   $latest = $wpdb->get_results( "SELECT * FROM `{$table_name}` WHERE `timestamp`= $timestamp ORDER BY `id` DESC", ARRAY_A );

   return $latest;   // returns an array with the latest devices
                     // All data

   //echo '<h3>Latest</h3>';
   //echo '<pre><code>';
   //var_dump( $latest );
   //echo '</code></pre>';
   
   // Show error if any
   $wpdb->print_error();
}

/**
 * =========================
 * Add data to the database
 * =========================
 */

/**
 * After fetching data via an API call, add it to
 * one of the two custom tables we've created. This 
 * allows us to reduce the number of API calls we 
 * make each time we need to display the data, and 
 * avoid API limits. 
 */

function bd_foobot_update_db_device( $device_api_data ){

   global $wpdb;
   // Turn on errors display
   //$wpdb->show_errors();

   $table_name = $wpdb->prefix . 'bd_foobot_device_data';
   $time = current_time('timestamp');

   // Loop each device
   foreach( $device_api_data as $data ){
      $device_data = array();
      //echo '<pre><code>';
      //var_dump( $data );
      //echo '</code></pre>';

      // Loop the device data
      foreach ( $data as $key => $value ){
         $device_data[] = array( $key => $value );
         //echo '<pre><code>';
         //var_dump( $key. ' ' .$value );
         //echo '</code></pre>';

         //echo '<h5>Device data in loop</h5>';
         //echo '<pre><code>';
         //var_dump( $device_data );
         //echo '</code></pre>';
      }
      //echo '<h5>Device data after loop</h5>';
      //echo '<pre><code>';
      //var_dump( $device_data );
      //echo '</code></pre>';
      
      // vars
      $uuid    = $device_data[0]['uuid'];
      $userId  = $device_data[1]['userId'];
      $mac     = $device_data[2]['mac'];
      $name    = $device_data[3]['name'];

      //echo '<pre><code>';
      //var_dump( $uuid );
      //echo '</code></pre>';
      
      // Insert data into db table
      $wpdb->insert( 
         $table_name, 
         array(
            'timestamp' => $time,
            'name' => $name,
            'uuid' => $uuid,
         ),
         array(
            '%d',
            '%s',
            '%s'
         )
      );

      error_log("EVENT: Device data inserted in table", 0);

      // Show error if any
      //$wpdb->print_error();

   }
}

function bd_foobot_update_db_sensors( $api_data ){

   global $wpdb;
   
   // DEBUG
   $wpdb->show_errors(); // Turn on errors display

   $table_name = $wpdb->prefix . 'bd_foobot_sensor_data';
   $time = current_time('timestamp');

   // Loop through each sensor
   foreach( $api_data as $data ){
      $db_data = array();

      // Loop the specific sensor data
      foreach ( $data as $key => $value ){
         $db_data[] = array( $key => $value );
      }
      
      // vars
      $uuid    = $db_data[0]['uuid'];
      $userId  = $db_data[1]['userId'];
      $mac     = $db_data[2]['mac'];
      $name    = $db_data[3]['name'];
      
      // Insert data into db table
      $wpdb->insert( 
         $table_name, 
         array(
            'timestamp'    => $time,
            'name'         => $name,
            'uuid'         => $uuid,
         ),
         array(
            '%d',
            '%s',
            '%s'
         )
      );

      error_log("EVENT: Sensor data inserted in table", 0);

      // DEBUG
      $wpdb->print_error(); // Show error if any

   }
}

function bd_foobot_update_device_data(){
   /**
    * Request an API call
    * (checks if transient set, if
    * not, makes API call)
    */
   $data = bd_foobot_get_device_data();

   if( $data ){
      /**
       * If the request returns data
       * (i.e. transient not set)
       * update the database
       */
      bd_foobot_update_db_device( $data );
   }

}

function bd_foobot_update_sensor_data()
{
   /**
    * Request an API call
    * (checks if transient set, if
    * not, makes API call)
    */
      $data = bd_foobot_get_sensor_data();

      if( $data ){
         /**
          * If the request returns data
          * (i.e. transient not set)
          * update the database
          */
         bd_foobot_update_db_sensor( $data );
      }
}
