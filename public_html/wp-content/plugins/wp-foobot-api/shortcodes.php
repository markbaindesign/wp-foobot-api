<?php

/**
 * Shortcodes
 */

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

function bd_foobot_show_api_key()
{
   
   $key = bd_foobot_get_api_key();
   ob_start();
	echo '<pre><code>'. $key . '</code></pre>';
	$content =  ob_get_contents();
	ob_clean();
	return $content;
}
add_shortcode('foobot_show_key', 'bd_foobot_show_api_key');

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
add_shortcode('foobot_show_device', 'bd_foobot_show_device_data');

/**
 * Show device name
 */
function bd_foobot_show_device_name()
{   
	$name = bd_get_foobot_device_name();
   ob_start();
	echo $name;
	$content =  ob_get_contents();
	ob_clean();
	return $content;
}
add_shortcode('foobot_device_name', 'bd_foobot_show_device_name');

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
add_shortcode('foobot_device_data', 'bd_foobot_show_data_from_device');

/**
 * Show temp now
 */
function bd_foobot_show_temp_now()
{   
	
	// Retrieve the sensor data from the database
	$data = bd_get_temp_now();

	echo '<pre><code>';
	var_dump( $data );
	echo '</code></pre>';

	// Time
	$timestamp = $data[0];
	$date = date('Y-m-d', $timestamp);
	$time = date('H:i:s', $timestamp);

	$temp = $data[1];
	// Rounding
	$tr = round($temp, 1);

	$units = $data[2];

	ob_start();
	echo '<div class="current_temp">Current temperature:</div>';
	echo '<h1>' . $tr . $units . '</h1>';
	echo '<div class="timestamp">Data read on ' . $date . ' at ' . $time . ' (UTC)</div>';

	// Debug
	// echo '<pre><code>';
	// var_dump( $data );
	// echo '</code></pre>';

	$content =  ob_get_contents();

	ob_clean();
	return $content;
}
add_shortcode('foobot_temp_now', 'bd_foobot_show_temp_now');