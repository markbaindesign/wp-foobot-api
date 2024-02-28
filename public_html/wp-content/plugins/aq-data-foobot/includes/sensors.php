<?php

/**
 * =============================
 * Get readings from the sensors
 * =============================
 */

// Show the data from a specific device
function bd_foobot_show_sensors( $device_name )
{

  // Get the target device UUID
  $uuid = bd_get_foobot_device_uuid( $device_name );

  // No device found
  if($uuid ==='error_device_not_found' || $uuid ==='' || $uuid ===NULL){
    $content = '<div class="foobot-data foobot-data__error">';
    $content.= sprintf(
      __('Sorry, the device "%s" has not been found. Please check your device name for accuracy e.g. extra spaces at the end, and try again.', 'aq-data-foobot'), $device_name
     );
    $content.='</div>';
    return $content;
  }
  
  // Fetch the sensor data from the database
  $sensor_data = bd_foobot_fetch_db_sensors( $uuid );

  if (count($sensor_data)> 999){

    // Remove one level from the array
    $data = $sensor_data[0];

   // Data time & date
   $utc_timestamp = '';
   if(isset($data['timestamp'])){
      $utc_timestamp = $data['timestamp'];
      $local_timestamp = bd324_get_local_datetime($utc_timestamp);
   }

   // Pretty up the data
   $Tmp_data =   number_format( $data['datapointTmp'],         1 );
   $Pm_data =    round( $data['datapointPm'],                  0 );
   $Co2_data =   round( $data['datapointCo2'],                 0 );
   $Voc_data =   round( $data['datapointVoc'],                 0 );
   $Hum_data =   round( $data['datapointHum'],                 1 );
   $All_data =   round( $data['datapointAllpollu'],            0 );


    // Output sensor data
    $content = '<div class="foobot-data"><ul class="sensors">';
    $content.= '<li class="sensor sensor--tmp"><span class="sensor__label">' . __('Temperature', 'aq-data-foobot') . '</span><span class="sensor__data">' . $Tmp_data . '</span><span class="sensor__unit">' . $data['unitTmp'] . '</span></li>' ;
    $content.= '<li class="sensor sensor--pm"><span class="sensor__label">' . __('PM', 'aq-data-foobot') . '</span><span class="sensor__data">' . $Pm_data . '</span><span class="sensor__unit">µg/m3</span></li>' ;
    $content.= '<li class="sensor sensor--co2"><span class="sensor__label">' . __('Co2', 'aq-data-foobot') . '</span><span class="sensor__data">' . $Co2_data . '</span><span class="sensor__unit">' . $data['unitCo2'] . '</span></li>' ;
    $content.= '<li class="sensor sensor--voc"><span class="sensor__label">' . __('VOC', 'aq-data-foobot') . '</span><span class="sensor__data">' . $Voc_data . '</span><span class="sensor__unit">' . $data['unitVoc'] . '</span></li>' ;
    $content.= '<li class="sensor sensor--hum"><span class="sensor__label">' . __('Humidity', 'aq-data-foobot') . '</span><span class="sensor__data">' . $Hum_data . '</span><span class="sensor__unit">' . $data['unitHum'] . '</span></li>' ;
    $content.= '<li class="sensor sensor--all"><span class="sensor__label">' . __('All', 'aq-data-foobot') . '</span><span class="sensor__data">' . $All_data . '</span><span class="sensor__unit">' . $data['unitAllpollu'] . '</span></li>' ;
    $content.= '</ul>';

    // Show device name and timestamp
   if ($utc_timestamp) {
      $content.= sprintf( __('<div class="sensor__data-age">Data from <span class="device-name">%s</span> updated <span class="data-timestamp">%s</span></div>', 'aq-data-foobot'), $device_name, $local_timestamp );
   }

    $content.= '</div>';
  } else {
    // Error message
    $content = '<div class="foobot-data foobot-data__error">';
    $content.= sprintf(
      __('The requested device has been found ("%s" UUID %s), but no data has been sent. Please confirm your device is working via the Foobot app or dashboard.', 'aq-data-foobot'), $device_name, $uuid
     );
    $content .= '</div>';

  }

  return $content;



}

/** 
 * Get current temperature and return it as array
 */
function bd_get_temp_now($uuid)
{
   /**
    * First, we need to check our transient and update the data in the 
    * custom table if necessary. 
    */
    bd_foobot_update_sensor_data($uuid);

   /**
    * Having done that, we can proceed with questioning the database.
    */

   $data = bd_foobot_fetch_latest_sensor_data();

   return $data;
}
