<?php

/**
 * Test data
 */

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