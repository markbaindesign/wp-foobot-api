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
  error_log("SHORTCODE: [foobot-show-data]", 0);
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
  return $content;

}
add_shortcode('foobot-show-data', 'bd_foobot_shortcode_show_sensors');


function bd_foobot_show_latest_sensor_data()
{
   
   $data = bd_foobot_fetch_latest_sensor_data();
	ob_start();
	
	// Debug
	echo '<pre><code>';
	var_dump( $data );
	echo '</code></pre>';

	$content =  ob_get_contents();
	ob_clean();
	return $content;
}
add_shortcode('foobot_show_latest_data', 'bd_foobot_show_latest_sensor_data');

function bd_foobot_show_device_data()
{
   
   $device = bd_get_foobot_device();
   ob_start();
	echo '<pre><code>';
	var_dump( $device );
	echo '</code></pre>';
	$content =  ob_get_contents();
	ob_clean();
	return $content;
}
// add_shortcode('foobot_show_device', 'bd_foobot_show_device_data');

/**
 * Show Foobot device data
 */
function bd_foobot_show_data_from_device()
{   
   $device = bd_get_foobot_data();
   ob_start();
	echo '<pre><code>';
	var_dump( $device );
	echo '</code></pre>';
	$content =  ob_get_contents();
	ob_clean();
	return $content;
}
// add_shortcode('foobot_device_data', 'bd_foobot_show_data_from_device');

/**
 * Show temp now
 */
function bd_foobot_show_temp_now( $atts )
{
    // [foobot_temp_now device="Bainbot"]

    $device_data = shortcode_atts( array(
        'device' => '',
	), $atts );

	$device_name = $device_data["device"];
	
	// Retrieve the sensor data from the database
	$data = bd_get_temp_now( $device_name );

	// Time
	$timestamp = $data['time'];
	$date = date('Y-m-d', strtotime( $timestamp) );
	$time = date('H:i:s', strtotime( $timestamp) );

	$temp = $data['datapointTmp'];
	// Rounding
	$tr = round($temp, 1);

	$units = $data['unitTmp'];
	$device = $data['device'];

	ob_start();

	// Debug
	echo '<pre><code>';
	var_dump( $data );
	echo '</code></pre>';

	echo '<div class="current_temp">Current location temperature:</div>';
	echo '<h1>' . $tr . $units . '</h1>';
	echo '<div class="timestamp"><small>Data from '.$device.' on ' . $date . ' at ' . $time . ' (UTC)</small></div>';

	// Debug
	// echo '<pre><code>';
	// var_dump( $data );
	// echo '</code></pre>';

	$content =  ob_get_contents();

	ob_clean();
	return $content;
}
add_shortcode('foobot_temp_now', 'bd_foobot_show_temp_now');




     /**
   * Show the latest device data from the database (Shortcode)
   * 
   * Test-only
   * 
   *  */ 

  function bd_foobot_shortcode_test_get_uuid( $atts )
  {
    // [foobot-get-uuid device="Bainbot"]

    $device_data = shortcode_atts( array(
        'device' => '',
    ), $atts );
    
    $device_name = $device_data["device"]; 
    // Debug
    echo '<pre><code>';
    var_dump( $device_name );
    echo '</code></pre>';
      
    $device_uuid = bd_get_foobot_device_uuid( $device_name );
    // Debug
    echo '<pre><code>';
    var_dump( $device_uuid );
    echo '</code></pre>';  

    ob_start();
        echo 'Device UUID: ' . $device_uuid;
    $content =  ob_get_contents();
    ob_clean();
    return $content;

  }
  add_shortcode('foobot_get_uuid', 'bd_foobot_shortcode_test_get_uuid');




 /* Shortcode to display tests */
 function bd_foobot_tests_shortcode()
 {
    $data = bd_foobot_test_device_data();

    // Add the test device data to the db
    bd_foobot_add_db_devices( $data );

     ob_start();
     
     // Debug
     echo '<h3>bd_foobot_test_device_data()</h3>';
     echo '<pre><code>';
     var_dump( $data );
     echo '</code></pre>';
 
     $content =  ob_get_contents();
     ob_clean();
     return $content;
 }
 add_shortcode('foobot_tests', 'bd_foobot_tests_shortcode');

 /**
 * =========================
 * API Calls
 * =========================
 */

  /**
   * Call the Device API & add to DB (Shortcode)
   * 
   * Test-only
   * 
   *  */ 

  function bd_foobot_shortcode_call_device_api()
  {
    $data = bd_foobot_call_api_trans_devices();    // Real API data with transients

    // ========= Use with CAUTION! =======//
    //$data = bd_foobot_call_api_devices();      // Real API data (no transients)
    //error_log("{{{ WARNING: Device API called without transients }}}", 0);

    // Add the test device data to the db
    // bd_foobot_add_db_devices( $data );
    
    echo '<pre><code>';
    var_dump( $data );
    echo '</code></pre>';

  }
  add_shortcode('foobot_device_api_test', 'bd_foobot_shortcode_call_device_api');

    /**
   * Call the Device API & add to DB (Shortcode)
   * 
   * Test-only
   * 
   *  */ 

  function bd_foobot_shortcode_call_sensor_api()
  {
    $data = bd_foobot_call_api_trans_devices();    // Real API data with transients

    // ========= Use with CAUTION! =======//
    $data = bd_foobot_call_api_devices();      // Real API data (no transients)
    //error_log("{{{ WARNING: Device API called without transients }}}", 0);

    // Add the test device data to the db
    // bd_foobot_add_db_devices( $data );
    
    echo '<pre><code>';
    var_dump( $data );
    echo '</code></pre>';

  }
  add_shortcode('foobot_sensor_api_test', 'bd_foobot_shortcode_call_sensor_api');
  

    /**
   * Show the latest device data from the database (Shortcode)
   * 
   * Test-only
   * 
   *  */ 

  function bd_foobot_shortcode_show_latest_device_data()
  {
    $data = bd_foobot_get_current_devices();

    ob_start();
    
    //echo '<pre><code>$data = bd_foobot_get_current_devices();</code></pre>';
    //echo 'returns this:';
    //echo '<pre><code>';
    //var_dump( $data );
    //echo '</code></pre>';

    // Display table of devices and UUIDs
    echo '<h3>Foobot Devices</h3>';
    echo '<p>The following active Foobot devices have been detected on your account. New devices can take up to 24 hours to appear.</p>';
    echo '<table><tr><th>Name</th><th>UUID</th></tr>';
    foreach( $data as $device ){
        // vars
        $timestamp  = $device["timestamp"];
        $name       = $device["name"];
        $uuid       = $device["uuid"];

        // table row
        echo '<tr>';
        echo '<td>' . $name . '</td>';
        echo '<td>' . $uuid . '</td>';
        echo '</tr>';
    }

    echo '</tr></table>';

    // Show timestamp
    echo '<p><small>' . $timestamp . '</small></p>';

    $content =  ob_get_contents();
    ob_clean();
    return $content;

  }
  add_shortcode('foobot_device_data_test', 'bd_foobot_shortcode_show_latest_device_data');