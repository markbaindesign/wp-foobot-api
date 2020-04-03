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