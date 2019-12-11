<?php

/**
 * Database tests
 */

function bdf_sc_test_fetch_api_device( $atts )
{
  // [foobot-get-data-test device="BainBot"]
  error_log("FUNCTION: bdf_sc_test_fetch_api_device", 0);

  $device_data = shortcode_atts( array(
      'device' => '',
  ), $atts );
  
  $device_name = $device_data["device"];

  // Get the device UUID
  $uuid = bd_get_foobot_device_uuid( $device_name );
  bd_pretty_debug( $uuid, "uuid" );
  
  // Fetch the sensor data from the database
  $sensor_data = bd_foobot_fetch_db_sensors( $uuid );
  $data = $sensor_data[0];
  bd_pretty_debug( $data, "data" );

  // Add the data to the database
  // bd_foobot_update_db_sensors( $data );

  // Output
  ob_start();
  echo '<ul class="sensors">';
  echo '<li class="sensor sensor--tmp"><span class="sensor__label">Temperature</span><span class="sensor__data">' . $data['datapointTmp'] . '</span><span class="sensor__unit">' . $data['unitTmp'] . '</span></li>' ;
  echo '<li class="sensor sensor--pm"><span class="sensor__label">PM</span><span class="sensor__data">' . $data['datapointPm'] . '</span><span class="sensor__unit">' . $data['unitPm'] . '</span></li>' ;
  echo '<li class="sensor sensor--co2"><span class="sensor__label">Co2</span><span class="sensor__data">' . $data['datapointCo2'] . '</span><span class="sensor__unit">' . $data['unitCo2'] . '</span></li>' ;
  echo '<li class="sensor sensor--voc"><span class="sensor__label">VOC</span><span class="sensor__data">' . $data['datapointVoc'] . '</span><span class="sensor__unit">' . $data['unitVoc'] . '</span></li>' ;
  echo '<li class="sensor sensor--hum"><span class="sensor__label">Humidity</span><span class="sensor__data">' . $data['datapointHum'] . '</span><span class="sensor__unit">' . $data['unitHum'] . '</span></li>' ;
  echo '<li class="sensor sensor--all"><span class="sensor__label">All</span><span class="sensor__data">' . $data['datapointAllpollu'] . '</span><span class="sensor__unit">' . $data['unitAllpollu'] . '</span></li>' ;
  echo '</ul>';

  
  $content =  ob_get_contents();
  ob_clean();
  return $content;

}
add_shortcode('foobot-get-data-test', 'bdf_sc_test_fetch_api_device');