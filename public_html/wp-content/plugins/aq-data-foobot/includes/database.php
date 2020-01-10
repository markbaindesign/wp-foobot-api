<?php

/**
 * Set database versions
 */

// Device
global $bd_foobot_device_db_version;
$bd_foobot_device_db_version = '1.2';

// Sensors
global $bd_foobot_sensor_db_version;
$bd_foobot_sensor_db_version = '1.4';

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
      timestamp int NOT NULL,
      uuid tinytext NOT NULL,
		unitTime tinytext NULL,
		datapointTime float NULL,
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

   add_option('bd_foobot_device_db_version', $bd_foobot_device_db_version);
}

/**
 * ============================
 * Fetch data from the database
 * ============================
 */

// Fetch sensor data
function bd_foobot_fetch_latest_sensor_data()
{

   // To DO
   // Pass the sensor you want to this function

   // Vars
   global $wpdb;
   $table_name = $wpdb->prefix . 'bd_foobot_sensor_data';

   // $data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table_name}` ORDER BY `id` DESC LIMIT 1", $sensor) );
   $data = $wpdb->get_row("SELECT * FROM `{$table_name}` ORDER BY `id` DESC LIMIT 1", ARRAY_A);

   return $data;
}

// Query the database for sensor 
// data from a specific device
function bd_foobot_fetch_db_sensors($uuid)
{

   // Debug
   error_log("FUNCTION: bd_foobot_fetch_db_sensors(" . $uuid . ")", 0);

   global $wpdb;
   $wpdb->show_errors();

   // Vars
   $table_name = $wpdb->prefix . 'bd_foobot_sensor_data';

   // Update the device table if required
   bd_foobot_update_sensor_data($uuid);

   // Now we query the db.
   $data = $wpdb->get_results("SELECT * FROM `{$table_name}` WHERE `uuid`='$uuid' ORDER BY `id` DESC LIMIT 1", ARRAY_A);

   return $data;

   // Show error if any
   $wpdb->print_error();
}

// Fetch device data
function bd_foobot_fetch_db_devices()
{

   // debug
   error_log("FUNCTION: bd_foobot_fetch_db_devices()", 0);

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
   $data = $wpdb->get_row("SELECT * FROM `{$table_name}` ORDER BY `id` DESC LIMIT 1", ARRAY_A);

   $timestamp = $data["timestamp"];

   //$latest = array();
   $latest = $wpdb->get_results("SELECT * FROM `{$table_name}` WHERE `timestamp`= $timestamp ORDER BY `id` DESC", ARRAY_A);
   if (count($latest) > 0) {
      return $latest;   // returns an array with the latest devices
   } else {
      return;
   }

   //echo '<h3>Latest</h3>';
   //echo '<pre><code>';
   //var_dump( $latest );
   //echo '</code></pre>';

   // Show error if any
   // $wpdb->print_error();
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

// Add device data to database
function bd_foobot_add_db_devices($device_api_data)
{

   error_log("FUNCTION: bd_foobot_add_db_devices", 0);

   global $wpdb;
   // Turn on errors display
   //$wpdb->show_errors();

   $table_name = $wpdb->prefix . 'bd_foobot_device_data';
   $time = current_time('timestamp');

   // Loop each device
   foreach ($device_api_data as $data) {
      $device_data = array();
      //echo '<pre><code>';
      //var_dump( $data );
      //echo '</code></pre>';

      // Loop the device data
      foreach ($data as $key => $value) {
         $device_data[] = array($key => $value);
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

      error_log("EVENT: Device data inserted in table for " . $uuid, 0);

      // Show error if any
      //$wpdb->print_error();

   }
}

// Add sensor data to database
function bd_foobot_add_db_sensors($data)
{

   global $wpdb;

   // DEBUG
   // $wpdb->show_errors(); // Turn on errors display

   $table_name = $wpdb->prefix . 'bd_foobot_sensor_data';

   // Vars
   $time                   = $data['start'];
   $uuid                   = $data['uuid'];

   // Units
   $unitPm                 = $data['units'][1];
   $unitTmp                = $data['units'][2];
   $unitHum                = $data['units'][3];
   $unitCo2                = $data['units'][4];
   $unitVoc                = $data['units'][5];
   $unitAllpollu           = $data['units'][6];

   // Datapoints
   $datapointPm            = $data['datapoints'][0][1];
   $datapointTmp           = $data['datapoints'][0][2];
   $datapointHum           = $data['datapoints'][0][3];
   $datapointCo2           = $data['datapoints'][0][4];
   $datapointVoc           = $data['datapoints'][0][5];
   $datapointAllpollu      = $data['datapoints'][0][6];

   // Insert data into db table
   $wpdb->insert(
      $table_name,
      array(
         'timestamp'          => $time,
         'uuid'               => $uuid,
         'unitPm'             => $unitPm,
         'datapointPm'        => $datapointPm,
         'unitTmp'            => $unitTmp,
         'datapointTmp'       => $datapointTmp,
         'unitHum'            => $unitHum,
         'datapointHum'       => $datapointHum,
         'unitCo2'            => $unitCo2,
         'datapointCo2'       => $datapointCo2,
         'unitVoc'            => $unitVoc,
         'datapointVoc'       => $datapointVoc,
         'unitAllpollu'       => $unitAllpollu,
         'datapointAllpollu'  => $datapointAllpollu,
      ),
      array(
         '%d', // 'timestamp'
         '%s', // 'uuid'
         '%s', // 'unitPm'
         '%f', // 'datapointPm'
         '%s', // 'unitTmp'
         '%f', // 'datapointTmp'
         '%s', // 'unitHum'
         '%f', // 'datapointHum'
         '%s', // 'unitCo2'
         '%f', // 'datapointCo2'
         '%s', // 'unitVoc'
         '%f', // 'datapointVoc'
         '%s', // 'unitAllpollu'
         '%f', // 'datapointAllpollu'
      )
   );

   error_log("EVENT | Database: New sensor data added", 0);

   // DEBUG
   // $wpdb->print_error(); // Show error if any

}

function bd_foobot_update_device_data()
{
   /**
    * Request an API call
    * (checks if transient set, if
    * not, makes API call)
    */
   $data = bd_foobot_call_api_trans_devices();

   if ($data) {
      /**
       * If the request returns data
       * (i.e. transient not set)
       * update the database
       */
      bd_foobot_add_db_devices($data);
   }
}

function bd_foobot_update_sensor_data($uuid)
{
   /**
    * Request an API call
    * (checks if transient set, if
    * not, makes API call)
    */
   $data = bd_foobot_call_api_trans_sensors($uuid);

   if ($data) {
      /**
       * If the request returns data
       * (i.e. transient not set)
       * update the database
       */
      bd_foobot_add_db_sensors($data);
   }
}
