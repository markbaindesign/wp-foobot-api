<?php

// TO DO

/**
 * Get device names
 */
function bd_get_foobot_device_name()
{
   $devices = bd_foobot_get_current_devices();
   // $name = $device[0]->{"name"};
   
   $name = 'BainBot';
   return $name;
}

/** 
 * Get device UUID
 * ===============
 * For use in shortcode where the user
 * gives the name of the device they want 
 * to get the data from.
 */

function bd_get_foobot_device_uuid( $device_name )
{
   $devices = bd_foobot_get_current_devices();

   // Get array columns
   $col = array_column( $devices, 'name' );
   
   // Get the array key
   $name = $device_name . ' ';   // API returns device names
                                 // with a trailing space.

   $key = array_search( $name, $col );
   if( $key===false ){
      return 'Device "' . $device_name . '" not found';
   }

   $uuid = $devices[$key]["uuid"];

   return $uuid;
}
