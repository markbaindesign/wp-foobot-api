<?php

/** 
 * Get device UUID
 * ===============
 * For use in shortcode where the user
 * gives the name of the device they want 
 * to get the data from.
 */

function bd_get_foobot_device_uuid( $device_name )
{
   // DEBUG
   if (BD0019__DEBUG === 1){
      error_log(sprintf("Getting the UUID for %s", $device_name, true));
   }

   $devices = bd_foobot_fetch_db_devices();
   
   // Get array columns
   $col = array_column( $devices, 'name' );
   
   // Get the array key
   $name = $device_name;

   $key = array_search( $name, $col );
   if( $key===false ){
      return 'error_device_not_found';
   } else {
      $uuid = $devices[$key]["uuid"];   
      return $uuid;
   }


}

/**
 * Get the local timestamp
 *
 * Convert the UTC timestamp of the data to the 
 * format in WordPress settings.
 * 
 * @param   $utc_timestamp       Data timestamp from API call
 * @return  $output              Local timestamp
 * 
 */
if(!function_exists('bd324_get_local_datetime')):
   function bd324_get_local_datetime($utc_timestamp)
   {

      /**
       * Get the date/time of last API call
      * 
      * Timezone of Foobot timestamp?  UTC?
      *
      * Requires WordPress >= 5.3
      *
      * See https://developer.wordpress.org/reference/functions/get_date_from_gmt/
      */
      
      /* Vars */
      $output = '';
      $date_format                  = get_option( 'date_format' );
      $time_format                  = get_option( 'time_format' );
      $output_format                = $date_format . ' ' . $time_format;
      $utc_timestamp_converted      = date($output_format, $utc_timestamp);
      $output                       = get_date_from_gmt( $utc_timestamp_converted, $output_format );
      return $output;
   }
endif;

/**
 * Get Device name from UUID
 * 
 * Looks up the given UUID in the Device database table,
 * and returns the Device Name if found.
 *
 * @param   string $uuid            Device UUID
 * @return  string $output          Device name
 */
if(!function_exists('bd324_get_device_name_from_uuid')):
   function bd324_get_device_name_from_uuid($uuid)
   {
      global $wpdb;

      /* Vars */
      $output        = '';
      $table_name    = BD0019__DEVICE_DB_TABLE;
   
      // Build query
      $query = $wpdb->prepare("
         SELECT * 
         FROM %i
            WHERE `uuid` = %s
            LIMIT 1
         ", $table_name, $uuid
      );
   
      // Now we query the db.
      $device_data = $wpdb->get_row( $query, ARRAY_A );
      $device_name = $device_data['name'];

      // DEBUG
      if (BD0019__DEBUG === 1){
         error_log(sprintf("Fetching device name for %s from %s", $uuid, $table_name));
      }

      if ($device_name){
         // DEBUG
         if (BD0019__DEBUG === 1){
            error_log(print_r($device_name, true));
         }
         $output = $device_name;
      } else {
         // DEBUG
         if (BD0019__DEBUG === 1){
            error_log(sprintf("Device name not found for %s", $uuid, true));
            error_log(print_r($device_name, true));
            $wpdb->print_error();
         }
         return;
      }

      return $output;
   }
endif;

