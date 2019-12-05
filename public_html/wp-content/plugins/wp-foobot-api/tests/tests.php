<?php
/**
 * Tests
 */

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