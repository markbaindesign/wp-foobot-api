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
   // Debug
   echo '<pre><code>';
   var_dump( $device_name );
   echo '</code></pre>';

   // Get array columns
   $col = array_column( $devices, 'name' );
   // Debug
   echo '<pre><code>';
   var_dump( $col );
   echo '</code></pre>';
   
   // Get the array key
   $name = $device_name . ' ';   // API returns device names
                                 // with a trailing space.
   // Debug
   echo '<pre><code>';
   var_dump( $name );
   echo '</code></pre>';

   //||//||//||//

   $key = array_search( $name, $col );
   if( $key===false ){
      // Debug
      echo '<pre><code>';
      var_dump( $key );
      echo '</code></pre>';
      return 'Device "' . $device_name . '" not found';
   }

   // Debug
   echo '<pre><code>';
   var_dump( $name );
   echo '</code></pre>';

   $uuid = $devices[$key]["uuid"];

   return $uuid;
}
