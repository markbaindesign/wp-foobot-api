<?php

/**
 * Admin screens
 */

add_action('admin_menu', 'baindesign_foobot_add_admin_menu');
add_action('admin_init', 'baindesign_foobot_settings_init');

function baindesign_foobot_add_admin_menu() {
	add_options_page( 
		'Foobot API Page', 
		'Foobot API', 
		'manage_options', 
		'foobot-api-page', 
		'baindesign_foobot_options_page'
	);
}

function baindesign_foobot_settings_init()
{
	// Register
	register_setting(
		'baindesignFoobot', 							// New options group
		'baindesign_foobot_api_settings'			// Entry in options table
	);

	add_settings_section(
		'baindesign-foobot-api-creds',						// Section ID
		__('Foobot API Credentials', '_bd_foobot'),		// Section header
		'baindesign_foobot_settings_section_callback',	// Callback
		'baindesignFoobot' 										// Add section to 
																		// options group
	);

	// API Key
	add_settings_field(
		'baindesign_foobot_api_key',						// ID
		__('API Key', '_bd_foobot'),						// Label
		'baindesign_foobot_api_key_field_render',		// Function to display inputs
		'baindesignFoobot',											// Page to display on
		'baindesign-foobot-api-creds'						// Section ID
	);

	// API username
	add_settings_field(
		'baindesign_foobot_api_user',					// ID
		__('API User', '_bd_foobot'),					// Label
		'baindesign_foobot_api_user_field_render',		// Function to display inputs
		'baindesignFoobot',									// Page to display on
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
	echo __('Add your Foobot API credentials below. An API key can be obtained at <a href="https://api.foobot.io/apidoc/index.html">api.foobot.io</a>.', '_bd_foobot');
}

/**
 * Render the options page form
 */

function baindesign_foobot_options_page() {
	?>
	<div class="wrap">
		<form action='options.php' method='post'>

			<h1>Foobot API Admin Page</h1>
			<?php
				settings_fields( 'baindesignFoobot' );
				do_settings_sections( 'baindesignFoobot' );
				submit_button();
			?>

		</form>
	</div>
	<?php
}
