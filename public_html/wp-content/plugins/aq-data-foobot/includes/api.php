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
      // DEBUG
      if (BD0019__DEBUG === 1){
         error_log($request->get_error_message(), true);
      }
      return false; // Bail early
   }

   $body = wp_remote_retrieve_body($request);

   $api_data = json_decode( $body, true);

   // DEBUG
   if (BD0019__DEBUG === 1) {
      error_log(print_r($api_data, TRUE));
   }

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
      // DEBUG
   if (BD0019__DEBUG === 1) {
      error_log(sprintf("New API call made for %s.", $uuid));
   }
   
   $key = bd_foobot_get_api_key();

   $url = 'https://api.foobot.io/v2/device/' . $uuid . '/datapoint/0/last/0/?' . $key;
   $args = array(
      'headers' => array(
         'X-API-KEY-TOKEN' => $key
      )
   );

   $request = wp_remote_get($url, $args);

   if (is_wp_error($request)) {
      // DEBUG
      if (BD0019__DEBUG === 1){
         error_log(print_r($request->get_error_message(), true));
      }
      return false; // Bail early
   }

   $body = wp_remote_retrieve_body($request);

   $api_data = json_decode( $body, true); // Output array

   // DEBUG
   if (BD0019__DEBUG === 1) {
      error_log(print_r($api_data, true));
   }

   return $api_data;
}

/**
 * Get device names and UUIDs
 * 
 * Use this function to request device data.
 * Uses transients to avoid repeated requests 
 * hitting API limit.
 * 
 * @return  array    $output           Device data (name, UUID)
 */
function bd_foobot_call_api_trans_devices()
{
   // Vars
   $output = '';
   $transient_id = 'foobot-api-device-updated';
   $transient = get_transient($transient_id);
   $expiry = 86400; // 24 hours

   // Check if transient already set / not expired
   // If set, return.
   if (1 === $transient) { // Transient set.
      // DEBUG
      if (BD0019__DEBUG === 1) {
         error_log(sprintf("Transient %s set. No API call made.", $transient_id));
      }
      return;
   } else {
      // Get the device data via new API call
      // DEBUG
      if (BD0019__DEBUG === 1) {
         error_log(sprintf("New API call made for devices."));
      }
      $data = bd_foobot_call_api_devices();

      if (is_wp_error($data)) {
         return; // Bail early
      } else {
         $output = $data;
      }

      // Set a transient
      // DEBUG
      if (BD0019__DEBUG === 1) {
         error_log(sprintf("Setting transient %s.", $transient_id));
      }
      set_transient($transient_id, 1, $expiry);
   }
   return $output;
}

/**
 * Get sensor data
 * 
 * Use this function to request sensor data from a specified device.
 * Uses transients to avoid repeated requests 
 * hitting API limit.
 * 
 * @param   string   $uuid             UUID of device
 * @return  array    $output           Sensor data
 */
function bd_foobot_call_api_trans_sensors($uuid)
{
   // Vars
   $output = '';
   $transient_id = 'foobot-api-data-updated-' . $uuid;
   $transient = get_transient($transient_id);
   $expiry = 600; // 10 mins

   // Check if transient already set / not expired
   // If set, return.
   if (1 === $transient) { // Transient set.
      // DEBUG
      if (BD0019__DEBUG === 1) {
         error_log(sprintf("Transient %s set for UUID %s. No API call made.", $transient_id, $uuid));
      }
      return;
   } else {
      // Get the device data via new API call
      $data = bd_foobot_call_api_sensors($uuid);

      // DEBUG
      if (BD0019__DEBUG === 1) {
         error_log(print_r($data, TRUE));
      }

      if (is_wp_error($data)) {
         return; // Bail early
      } else {
         $output = $data;
      }

      // Set a transient
      // DEBUG
      if (BD0019__DEBUG === 1) {
         error_log(sprintf("Setting transient %s.", $transient_id));
      }
      set_transient($transient_id, 1, $expiry);
   }
   return $output;
}