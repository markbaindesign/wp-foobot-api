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

   $api_data = json_decode($body);
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

   $api_data = json_decode($body);
   return $api_data;
}