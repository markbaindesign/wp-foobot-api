<?php

/**
 * =============================
 * Get readings from the sensors
 * =============================
 */

// Show the data from a specific device
function bd_foobot_show_sensors( $device_name )
{
  // debug
  // error_log("FUNCTION: bd_foobot_show_sensors (" .$device_name. ")", 0);

  // Get the target device UUID
  $uuid = bd_get_foobot_device_uuid( $device_name );
  if($uuid==='error_device_not_found'){
    $content = '<div class="foobot-data foobot-data__error">';
    $content.= sprintf(
      __('Sorry, the device "%s" has not been found. Please check your device name for accuracy e.g. extra spaces at the end, and try again.', 'aq-data-foobot'), $device_name
     );
    $content.='</div>';
    return $content;
  }
  
  // Fetch the sensor data from the database
  $sensor_data = bd_foobot_fetch_db_sensors( $uuid );

  if (count($sensor_data)> 0){

    // Remove one level from the array
    $data = $sensor_data[0];

   // Data time & date

   /**
    * Get the date/time of last API call
    * 
    * Timezone of Foobot timestamp?  UTC?
    *
    * See https://developer.wordpress.org/reference/functions/get_date_from_gmt/
    */
   $utc_timestamp                = $data['timestamp'];
   $date_format                  = get_option( 'date_format' );
   $time_format                  = get_option( 'time_format' );
   $output_format                = $date_format . ' ' . $time_format;
   $utc_timestamp_converted      = date($output_format, $utc_timestamp);
   $local_timestamp              = get_date_from_gmt( $utc_timestamp_converted, $output_format );
   error_log(print_r($local_timestamp, true));

    // Pretty up the data
    $Tmp_data = round( $data['datapointTmp'], 1 );
    $Pm_data = round( $data['datapointPm'], 1 );
    $Co2_data = round( $data['datapointCo2'], 1 );
    $Voc_data = round( $data['datapointVoc'], 1 );
    $Hum_data = round( $data['datapointHum'], 1 );
    $All_data = round( $data['datapointAllpollu'], 1 );


    // Output sensor data
    $content = '<div class="foobot-data"><ul class="sensors">';
    $content.= '<li class="sensor sensor--tmp"><span class="sensor__label">' . __('Temperature', 'aq-data-foobot') . '</span><span class="sensor__data">' . $Tmp_data . '</span><span class="sensor__unit">' . $data['unitTmp'] . '</span></li>' ;
    $content.= '<li class="sensor sensor--pm"><span class="sensor__label">' . __('PM', 'aq-data-foobot') . '</span><span class="sensor__data">' . $Pm_data . '</span><span class="sensor__unit">µg/m3</span></li>' ;
    $content.= '<li class="sensor sensor--co2"><span class="sensor__label">' . __('Co2', 'aq-data-foobot') . '</span><span class="sensor__data">' . $Co2_data . '</span><span class="sensor__unit">' . $data['unitCo2'] . '</span></li>' ;
    $content.= '<li class="sensor sensor--voc"><span class="sensor__label">' . __('VOC', 'aq-data-foobot') . '</span><span class="sensor__data">' . $Voc_data . '</span><span class="sensor__unit">' . $data['unitVoc'] . '</span></li>' ;
    $content.= '<li class="sensor sensor--hum"><span class="sensor__label">' . __('Humidity', 'aq-data-foobot') . '</span><span class="sensor__data">' . $Hum_data . '</span><span class="sensor__unit">' . $data['unitHum'] . '</span></li>' ;
    $content.= '<li class="sensor sensor--all"><span class="sensor__label">' . __('All', 'aq-data-foobot') . '</span><span class="sensor__data">' . $All_data . '</span><span class="sensor__unit">' . $data['unitAllpollu'] . '</span></li>' ;
    $content.= '</ul>';
    $content.= sprintf( __('<div class="sensor__data-age">Data from <span class="device-name">%s</span> updated <span class="data-timestamp">%s</span></div>', 'aq-data-foobot'), $device_name, $local_timestamp );
    $content.= '</div>';
  } else {
    // Error message
    $content = '<div class="foobot-data foobot-data__error">';
    $content .= __('Sorry, something went wrong. Please try again later', 'aq-data-foobot');
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
