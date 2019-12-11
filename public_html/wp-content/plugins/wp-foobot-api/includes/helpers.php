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
   $name = $device_name . ' ';   // API returns device names
                                 // with a trailing space.

   $key = array_search( $name, $col );
   if( $key===false ){
      return 'Device "' . $device_name . '" not found';
      error_log('Device "' . $device_name . '" not found', 0);
   }

   $uuid = $devices[$key]["uuid"];

   // debug
   error_log("FUNCTION: bd_get_foobot_device_uuid (" .$device_name. ")", 0);

   return $uuid;
}