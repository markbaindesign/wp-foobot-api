<?php

/**
 * This function should not be called directly!!!
 * ==============================================
 * 
 * Using our API key, we question the API to get the UUID of the 
 * Foobot device. With this info, we can go on to call sensor data. 
 * 
 * This retrieved data should then be added to the database.
 * 
 * We must never call this function directly! Instead, we must
 * retrieve the data we have stored in our custom database table (see 
 * "database.php"). This function is only used to update the 
 * table. 
 * 
 */
function bd_foobot_call_device_api()
{

   // Vars
   $key = bd_foobot_get_api_key();
   $user = bd_foobot_get_api_user();
   $url = 'https://api.foobot.io/v2/owner/' . $user . '/device/';
   $args = array('headers' => array('X-API-KEY-TOKEN' => $key));

   // Request
   $request = wp_remote_get($url, $args);

   if (is_wp_error($request)) {
      return false; // Bail early
   }

   $body = wp_remote_retrieve_body($request);

   $api_data = json_decode( $body, true);
   return $api_data;
}

/**
 * This function should not be called directly!!!
 * ==============================================
 * 
 * Get the API data
 * ================
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

function bd_foobot_call_data_api()
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

   $api_data = json_decode( $body, true); // Output array
   return $api_data;
}

/**
 * 
 * These functions use transients to avoid hitting the API
 * limit. 
 */

// Update device data
function bd_foobot_get_device_data()
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

   return $device_data;

   // Transient is set for 24 hours
   set_transient('foobot-api-device-updated', 1, (60 * 60 * 24));

   // Debug
   error_log("Foobot sensor data has been updated! Next update > 24 hours.", 0);
}

// Update sensor data
function bd_foobot_get_sensor_data()
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