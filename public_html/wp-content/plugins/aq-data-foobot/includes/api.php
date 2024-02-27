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

function bd_foobot_call_api_sensors( $uuid )
{
   $key = bd_foobot_get_api_key();

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
function bd_foobot_call_api_trans_devices()
{
   global $wpdb;

   // If an API call has been made within the last 24 hours, 
   // return.
   if (1 == get_transient('foobot-api-device-updated')) {
      return;
   }

   // Get the device data
   $device_data = bd_foobot_call_api_devices();

   // Transient is set for 24 hours
   set_transient('foobot-api-device-updated', 1, (60 * 60 * 24));

   return $device_data;
}

// Update sensor data
function bd_foobot_call_api_trans_sensors( $uuid )
{
   global $wpdb;

   // If an API call has been made within the last 5 mins, 
   // return.
   if (1 == get_transient('foobot-api-data-updated-' . $uuid )) {

      return;
   }

   // Get the device data
   $data = bd_foobot_call_api_sensors( $uuid );
   if (is_wp_error($data)) {
      return false; // Bail early
   }

   // Transient is set for 10 mins
   set_transient('foobot-api-data-updated-' . $uuid, 1, (60 * 10));

   return $data;
}