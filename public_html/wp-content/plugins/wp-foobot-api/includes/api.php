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
function bd_foobot_call_api_devices()
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

   // debug
   error_log("FUNCTION: bd_foobot_call_api_devices", 0);

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

function bd_foobot_call_api_sensors( $device_name )
{
   $key = bd_foobot_get_api_key();
   $uuid = bd_get_foobot_device_uuid( $device_name );

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

   // debug
   error_log("FUNCTION: bd_foobot_call_api_sensors", 0);
   return $api_data;
}

/**
 * 
 * These functions use transients to avoid hitting the API
 * limit. 
 */

// Update device data
function bd_foobot_call_api_trans_devices()
{
   global $wpdb;

   // debug
   error_log("FUNCTION: bd_foobot_call_api_trans_devices", 0);

   // If an API call has been made within the last 24 hours, 
   // return.
   if (1 == get_transient('foobot-api-device-updated')) {
      // Debug
      error_log("NOTICE: No Foobot Device API call made at this time.", 0);
      return;
   }

   // Get the device data
   $device_data = bd_foobot_call_api_devices();

   // Transient is set for 24 hours
   set_transient('foobot-api-device-updated', 1, (60 * 60 * 24));

   // Debug
   error_log("NOTICE: Foobot sensor data has been updated! Next update > 24 hours.", 0);

   return $device_data;
}

// Update sensor data
function bd_foobot_call_api_trans_sensors( $device_name )
{
   global $wpdb;

   // debug
   error_log("FUNCTION: bd_foobot_call_api_trans_sensors", 0);

   // If an API call has been made within the last 5 mins, 
   // return.
   if (1 == get_transient('foobot-api-data-updated')) {
      // Debug
      error_log("No Foobot Sensor API call made at this time.", 0);

      return;
   }

   // Get the device data
   $data = bd_foobot_call_api_sensors( $device_name );
   if (is_wp_error($data)) {
      error_log("Error: No data from Foobot sensor API ", 0);
      return false; // Bail early
   }

   return $data;

   // Transient is set for 5 mins
   set_transient('foobot-api-data-updated', 1, (60 * 5));

   // Debug
   error_log("Foobot sensor data has been updated! Next update > 5 mins.", 0);
}