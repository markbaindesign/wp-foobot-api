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

   $table_name = $wpdb->prefix . 'bd_foobot_api';

   $charset_collate = $wpdb->get_charset_collate();

   $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(55) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($sql);

   add_option('bd_foobot_db_version', $bd_foobot_db_version);
}

function bd_foobot_install_data()
{
   global $wpdb;

   $welcome_name = 'Mr. WordPress';
   $welcome_text = 'Congratulations, you just completed the installation!';

   $table_name = $wpdb->prefix . 'bd_foobot_api';

   $wpdb->insert(
      $table_name,
      array(
         'time' => current_time('mysql'),
         'name' => $welcome_name,
         'text' => $welcome_text,
      )
   );
}
