<?php

/**
 * =============================
 * Get readings from the sensors
 * =============================
 */

// Show the data from a specific device
function bd_fbt_show_data( $device_name )
{
  // Get the target device UUID
  $uuid = bd_get_foobot_device_uuid( $device_name );
  
  // Fetch the sensor data from the database
  $sensor_data = bdf_query_sensors( $uuid );

  // Remove one level from the array
  $data = $sensor_data[0];

  // Output sensor data
  $content = '<ul class="sensors">';
  $content.= '<li class="sensor sensor--tmp"><span class="sensor__label">Temperature</span><span class="sensor__data">' . $data['datapointTmp'] . '</span><span class="sensor__unit">' . $data['unitTmp'] . '</span></li>' ;
  $content.= '<li class="sensor sensor--pm"><span class="sensor__label">PM</span><span class="sensor__data">' . $data['datapointPm'] . '</span><span class="sensor__unit">' . $data['unitPm'] . '</span></li>' ;
  $content.= '<li class="sensor sensor--co2"><span class="sensor__label">Co2</span><span class="sensor__data">' . $data['datapointCo2'] . '</span><span class="sensor__unit">' . $data['unitCo2'] . '</span></li>' ;
  $content.= '<li class="sensor sensor--voc"><span class="sensor__label">VOC</span><span class="sensor__data">' . $data['datapointVoc'] . '</span><span class="sensor__unit">' . $data['unitVoc'] . '</span></li>' ;
  $content.= '<li class="sensor sensor--hum"><span class="sensor__label">Humidity</span><span class="sensor__data">' . $data['datapointHum'] . '</span><span class="sensor__unit">' . $data['unitHum'] . '</span></li>' ;
  $content.= '<li class="sensor sensor--all"><span class="sensor__label">All</span><span class="sensor__data">' . $data['datapointAllpollu'] . '</span><span class="sensor__unit">' . $data['unitAllpollu'] . '</span></li>' ;
  $content.= '</ul>';

  return $content;

}

/** 
 * Get current temperature and return it as array
 */
function bd_get_temp_now( $device_name )
{
   /**
    * First, we need to check our transient and update the data in the 
    * custom table if necessary. 
    */
    bd_foobot_update_sensor_data( $device_name );

   /**
    * Having done that, we can proceed with questioning the database.
    */

   $data = bd_foobot_fetch_latest_sensor_data();

   return $data;
}
