<?php

/**
 * Shortcodes
 * ==========
 * 
 * Display the data
 */


// Show the data from a specific device
function bd_foobot_shortcode_show_sensors( $atts )
{
  // e.g. [foobot-show-data device="BainBot"]

  // Debug
  error_log("== SHORTCODE: Start [foobot-show-data] ==", 0);
  error_log("FUNCTION: bd_foobot_shortcode_show_sensors", 0);


  // Get attributes from shortcode
  $device_data = shortcode_atts( array(
      'device' => '',
  ), $atts );
  
  // Store atts in var
  $device_name = $device_data["device"];

  // Show the data
  $output = bd_foobot_show_sensors( $device_name ); // sensors.php

  // Output sensor data
  ob_start();
  echo $output;
  
  $content =  ob_get_contents();
  ob_clean();

  // Debug
  error_log("== SHORTCODE: End [foobot-show-data] ==", 0);

  return $content;

}
add_shortcode('foobot-show-data', 'bd_foobot_shortcode_show_sensors');
