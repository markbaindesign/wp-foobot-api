<?php

if (!defined('ABSPATH')) {
   die('Invalid request.');
}

// Get API key
function bd_foobot_get_api_key()
{
   $key = BD0019__API_KEY;
   if ($key){
      return $key;
   } else {
      return "API key not found";
      // DEBUG
      if (BD0019__DEBUG === 1){
         error_log(print_r("API key not found", true));
      }
   }
}

// Get API username from the database
function bd_foobot_get_api_user()
{
   $user = BD0019__API_USER;
   if ($user){
      return $user;
   } else {
      return "API username not found";
      // DEBUG
      if (BD0019__DEBUG === 1){
         error_log(print_r("API user not found", true));
      }
   }
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

   $table_name = $wpdb->prefix . 'bd_foobot_sensor_data';

   $charset_collate = $wpdb->get_charset_collate();

   // DEBUG
   if (BD0019__DEBUG === 1){
      error_log(print_r("Creating table " . $table_name, true));
   }

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

   add_option('bd_foobot_sensor_db_version', BD0019__SENSOR_DB_VERSION);
}

// Create table to store device data
function bd_foobot_create_device_table()
{
   global $wpdb;

   $table_name = $wpdb->prefix . 'bd_foobot_device_data';

   $charset_collate = $wpdb->get_charset_collate();

   // DEBUG
   if (BD0019__DEBUG === 1){
      error_log(print_r("Creating table " . $table_name, true));
   }

   $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		timestamp int NOT NULL,
		name tinytext NOT NULL,
		uuid tinytext NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($sql);

   add_option('bd_foobot_device_db_version', BD0019__DEVICE_DB_VERSION);
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
   $data          = array();
   $table_name    = BD0019__SENSOR_DB_TABLE;

   // Build query
   $query = $wpdb->prepare("
      SELECT * 
      FROM %i
         ORDER BY 'id'
         DESC LIMIT 1
      ", $table_name,
   );

   // Now we query the db.
   $data = $wpdb->get_row( $query, ARRAY_A );

   // DEBUG
   if (BD0019__DEBUG === 1){
      error_log(print_r("Fetching data from table " . $table_name, true));
      $wpdb->print_error();
      error_log(print_r($data, true));
   }

   return $data;
}

// Query the database for sensor 
// data from a specific device
function bd_foobot_fetch_db_sensors($uuid)
{

   global $wpdb;

   // DEBUG
   if (BD0019__DEBUG === 1){$wpdb->show_errors();}

   // Vars
   $table_name = BD0019__SENSOR_DB_TABLE;

   // Update the device table if required
   bd_foobot_update_sensor_data($uuid);

   // Build query
   $query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE uuid = %s ORDER BY 'id' DESC LIMIT 1", $uuid);

   // Now we query the db.
   $data = $wpdb->get_results( $query, ARRAY_A );

   // DEBUG
   if (BD0019__DEBUG === 1){
      error_log(print_r("Fetching data from " . DB_NAME . " > " . BD0019__SENSOR_DB_TABLE, true));
      $wpdb->print_error();
      error_log(print_r($data, true));
   }

   return $data;

}

/**
 * Fetch device data from the database
 * 
 *    Devices on the user account are stored in a custom table.
 *    This is used to prevent hitting API limits.
 * 
 *    If the API has been called recently, we use this function
 *    instead to get the device data. 
 * 
 *    NOTE: This table is updates with devices each time the 
 *    API call is made. Devices are never removed. In order to
 *    get current devices, we need to check only the most recent.
 * 
 * @return array $latest_devices  Array of last devices returned by API.
 */
function bd_foobot_fetch_db_devices()
{

   global $wpdb;
   
   // DEBUG
   if (BD0019__DEBUG === 1){$wpdb->show_errors();}

   // Vars
   $table_name = BD0019__DEVICE_DB_TABLE;

   // Update the device table if required
   bd_foobot_update_device_data();

   // DEBUG
   if (BD0019__DEBUG === 1){
      error_log(sprintf("Fetching device data from database (%s)", $table_name, true));
   }

   // Get all the results
   // TO DO: Only return results from the last 24 hours?

   // Order the results

   // Get the most recent result and return rows that
   // match the same timestamp

   //$data = $wpdb->get_row( "SELECT * FROM `{$table_name}` WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)", ARRAY_A );

   $data = array();

   // Build query
   $query = $wpdb->prepare("
      SELECT * 
      FROM %i
         ORDER BY 'id'
         DESC LIMIT 1
      ", $table_name,
   );

   // Now we query the db.
   $data = $wpdb->get_row( $query, ARRAY_A );

   error_log(print_r("Most recent device found:", true));
   error_log(print_r($data, true));
   
   // Get timestamp of most recent API call.
   // We will use this to find all devices returned
   // in the last API call.
   $timestamp = $data["timestamp"];

   /**
    * Build new query to return rows that match timestamp
    */
   $query2 = $wpdb->prepare("
      SELECT * 
      FROM %i
         WHERE timestamp = %d
         ORDER BY 'id'
         DESC
      ", $table_name, $timestamp
   );

   // Run query
   $latest_devices = $wpdb->get_results( $query2, ARRAY_A );

   // Return results
   // DEBUG
   if (BD0019__DEBUG === 1){
      error_log(sprintf("All devices found that match timestamp %d: ", $timestamp, true));
      error_log(print_r($latest_devices, true));
   }
   if (count($latest_devices) > 0) {
      return $latest_devices; // returns an array with the latest devices
   } else {
      if (BD0019__DEBUG === 1){
         error_log(print_r("No devices found for timestamp %d.", $timestamp, true));
      }
      return;
   }


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

   global $wpdb;

   // DEBUG
   if (BD0019__DEBUG === 1){$wpdb->show_errors();}

   $table_name = $wpdb->prefix . 'bd_foobot_device_data';
   $time = current_time('timestamp');

   // Loop each device
   foreach ($device_api_data as $data) {
      $device_data = array();

      // Loop the device data
      foreach ($data as $key => $value) {
         $device_data[] = array($key => $value);
      }

      // vars
      $uuid    = $device_data[0]['uuid'];
      $userId  = $device_data[1]['userId'];
      $mac     = $device_data[2]['mac'];
      $name    = $device_data[3]['name'];


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

   }
}

// Add sensor data to database
function bd_foobot_add_db_sensors($data)
{

   global $wpdb;

   // DEBUG
   if (BD0019__DEBUG === 1){
      $wpdb->show_errors();
      error_log(print_r($data, true));
   };

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
      BD0019__SENSOR_DB_TABLE,
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

}

/**
 * Update device data
 */
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
