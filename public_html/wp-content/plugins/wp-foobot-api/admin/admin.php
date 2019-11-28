<?php

/**
 * Admin screens
 */

add_action('admin_init', 'baindesign_foobot_settings_init');

function baindesign_foobot_settings_init()
{

	register_setting(
		'discussion', 													// Existing options group
		'baindesign_foobot_api_settings'							// Entry in options table
	);

	add_settings_section(
		'baindesign-foobot-api-key',								// Section ID
		__('Foobot API Key', '_bd_foobot'),						// Section header
		'baindesign_foobot_settings_section_callback',		// Callback
		'discussion' 													// Add section to Settings > Discussion
	);

	/**
	 * Add a setting field for each language
	 */

	// Catalan
	add_settings_field(
		'baindesign_foobot_api_key',								// ID
		__('API Key', '_bd_foobot'),								// Label
		'baindesign_foobot_api_key_field_render',				// Function to display inputs
		'discussion',													// Page to display on
		'baindesign-foobot-api-key'								// Section ID where to show field
	);
}

/**
 * Render the input fields for each language
 */

// Catalan
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
	echo __('Add your Foobot API key below.', '_bd_foobot');
}
