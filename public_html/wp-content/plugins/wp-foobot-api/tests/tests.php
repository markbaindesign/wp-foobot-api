<?php
/**
 * Tests
 */
function bdf_sc_test_fetch_api_device( $atts )
{
  // [foobot-get-data-test device="BainBot"]

  $device_data = shortcode_atts( array(
      'device' => '',
  ), $atts );
  
  $device_name = $device_data["device"];

  // Get the device UUID
  $uuid = bd_get_foobot_device_uuid( $device_name );
  bd_pretty_debug( $uuid, "uuid" );
  
  // Fetch the sensor data from the database
  $sensor_data = bdf_query_sensors( $uuid );
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

 // Create test API data to test database
 function bd_foobot_test_device_data(){
     // This function returns an array that mimics
     // the data as returned via the Foobot API.
     // This test data prevents hitting the real
     // API too often during testing.
     $test_devices = array(      
        array(
            "uuid" => "123XYZ3210", 
            "name" => "TestBot ", 
        ), 
        array(
            "uuid" => "123ABC7890", 
            "name" => "TestBot2 ", 
        ),
        array(
            "uuid" => "99yrtZZC7890", 
            "name" => "TestBot3 ",
        )       
    );
    return $test_devices;
 }


 /* Shortcode to display tests */
 function bd_foobot_tests_shortcode()
 {
    $data = bd_foobot_test_device_data();

    // Add the test device data to the db
    bd_foobot_update_db_device( $data );

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
    $data = bd_foobot_get_device_data();    // Real API data with transients

    // ========= Use with CAUTION! =======//
    //$data = bd_foobot_call_device_api();      // Real API data (no transients)
    //error_log("{{{ WARNING: Device API called without transients }}}", 0);

    // Add the test device data to the db
    // bd_foobot_update_db_device( $data );
    
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
    $data = bd_foobot_get_device_data();    // Real API data with transients

    // ========= Use with CAUTION! =======//
    $data = bd_foobot_call_device_api();      // Real API data (no transients)
    //error_log("{{{ WARNING: Device API called without transients }}}", 0);

    // Add the test device data to the db
    // bd_foobot_update_db_device( $data );
    
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