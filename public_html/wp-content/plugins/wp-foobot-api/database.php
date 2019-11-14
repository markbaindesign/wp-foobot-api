<?php

/**
 * Create a custom table to hold our sensor data
 */

global $bd_foobot_db_version;
$bd_foobot_db_version = '1.0';

function bd_foobot_create_table()
{
   global $wpdb;
   global $bd_foobot_db_version;

   $table_name = $wpdb->prefix . 'bd_foobot_sensor_data';

   $charset_collate = $wpdb->get_charset_collate();

   $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      device tinytext NOT NULL,
		sensor tinytext NOT NULL,
		unit tinytext NOT NULL,
      datapoint float NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($sql);

   add_option('bd_foobot_db_version', $bd_foobot_db_version);
}

function bd_foobot_install_data()
{
   global $wpdb;

   $table_name = $wpdb->prefix . 'bd_foobot_sensor_data';

   $wpdb->insert(
      $table_name,
      array(
         'time' => '0000-00-00 00:00:00',
         'device' => 'MyDevice',
         'sensor' => 'tmp',
         'unit' => 'C',
         'datapoint' => '19.248',
      )
   );
}

/**
 * 
 */
function bd_foobot_update_temp_data()
{
   global $wpdb;

   $welcome_name = 'Mr. WordPress';
   $welcome_text = 'Congratulations, you just completed the installation!';

   $table_name = $wpdb->prefix . 'bd_foobot_data';

   $wpdb->insert(
      $table_name,
      array(
         'time' => current_time('mysql'),
         'name' => $welcome_name,
         'text' => $welcome_text,
      )
   );
}

/**
 * Fetch data for a particular sensor from the database
 */
function bd_foobot_fetch_latest_sensor_data(){
   
   // Vars
   global $wpdb;
   $table_name = $wpdb->prefix . 'bd_foobot_sensor_data';
   
   // $data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table_name}` ORDER BY `id` DESC LIMIT 1", $sensor) );
   $data = $wpdb->get_row( "SELECT * FROM `{$table_name}` ORDER BY `id` DESC LIMIT 1", ARRAY_A );

   return $data;

}