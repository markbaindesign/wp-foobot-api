<?php



/** 
 * Get current temperature and return it as array
 */
function bd_get_temp_now()
{
   /**
    * First, we need to check our transient and update the data in the 
    * custom table if necessary. 
    */
    bd_foobot_update_sensor_data();

   /**
    * Having done that, we can proceed with questioning the database.
    */

   $data = bd_foobot_fetch_latest_sensor_data();

   return $data;
}
