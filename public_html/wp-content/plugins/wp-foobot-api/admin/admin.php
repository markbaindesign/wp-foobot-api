<?php

/**
 * Admin screens
 */

add_action('admin_init', 'baindesign_foobot_settings_init');

function baindesign_foobot_settings_init()
{
	// Register
	register_setting(
		'discussion', 									// Existing options group
		'baindesign_foobot_api_settings'				// Entry in options table
	);

	add_settings_section(
		'baindesign-foobot-api-creds',					// Section ID
		__('Foobot API Credentials', '_bd_foobot'),		// Section header
		'baindesign_foobot_settings_section_callback',	// Callback
		'discussion' 									// Add section to Discussion
	);

	// API Key
	add_settings_field(
		'baindesign_foobot_api_key',					// ID
		__('API Key', '_bd_foobot'),					// Label
		'baindesign_foobot_api_key_field_render',		// Function to display inputs
		'discussion',									// Page to display on
		'baindesign-foobot-api-creds'					// Section ID
	);

	// API username
	add_settings_field(
		'baindesign_foobot_api_user',					// ID
		__('API User', '_bd_foobot'),					// Label
		'baindesign_foobot_api_user_field_render',		// Function to display inputs
		'discussion',									// Page to display on
		'baindesign-foobot-api-creds'					// Section ID
	);
}

function baindesign_foobot_api_user_field_render()
{
	$options = get_option( 'baindesign_foobot_api_settings' );
	?>
	<input type='email' name='baindesign_foobot_api_settings[baindesign_foobot_api_user]' placeholder='Your API email' value='<?php echo $options['baindesign_foobot_api_user']; ?>'>
<?php
}

function baindesign_foobot_api_key_field_render()
{
	$options = get_option( 'baindesign_foobot_api_settings' );
	?>
	<textarea rows="7" cols="50" name='baindesign_foobot_api_settings[baindesign_foobot_api_key]' placeholder='Your API key'><?php echo $options['baindesign_foobot_api_key']; ?></textarea>
<?php
}
/**
 * Render the settings section content
 */
function baindesign_foobot_settings_section_callback()
{
	echo __('Add your Foobot API credentials below.', '_bd_foobot');
}
