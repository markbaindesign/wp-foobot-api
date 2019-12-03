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
 * Add device data to database
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

function bd_foobot_update_device_data(){

   // Get the API data
   $data = bd_foobot_get_device_data();

   echo '<pre><code>';
   var_dump( $data );
   echo '</code></pre>';

   // Debug
   error_log("EVENT: An attempted API call has been made.", 0);
   
   // Update the database with the API data
   bd_foobot_update_db_device($data);

   // Debug
   error_log("EVENT: An attempted database update has been made.", 0);

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
   if (is_wp_error($sensor_data)) {
      error_log("Error: No data from Foobot sensor API ", 0);
      return false; // Bail early
   }

   // Add the API to the database
   $table_name = $wpdb->prefix . 'bd_foobot_sensor_data';
   foreach ( $sensor_data as $key => $value ){
      $wpdb->insert( $table_name, array( $key => $value ));
   }
   // vars
   $device=$unitTmp=$datapointTmp='';

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

/**
 * Get latest devices from the database
 */
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
	$data = $wpdb->get_row( "SELECT * FROM `{$table_name}` ORDER BY `id` DESC LIMIT 1", ARRAY_A );

   return $data;
   
   // Show error if any
   $wpdb->print_error();
}