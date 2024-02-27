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
   $devices = bd_foobot_fetch_db_devices();
   // Get array columns
   $col = array_column( $devices, 'name' );
   
   // Get the array key
   $name = $device_name;

   $key = array_search( $name, $col );
   if( $key===false ){
      return 'error_device_not_found';
      // error_log('Device "' . $device_name . '" not found', 0);
   } else {
      $uuid = $devices[$key]["uuid"];   
      return $uuid;

      // debug
      // error_log("FUNCTION: bd_get_foobot_device_uuid (" .$device_name. ")", 0);
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
