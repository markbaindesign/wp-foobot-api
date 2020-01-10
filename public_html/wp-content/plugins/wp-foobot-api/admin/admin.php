<?php

/**
 * Admin screens
 */

add_action('admin_menu', 'baindesign_foobot_add_admin_menu');
add_action('admin_init', 'baindesign_foobot_settings_init');

function baindesign_foobot_add_admin_menu()
{
	add_options_page(
		'Foobot API Page',
		'Foobot API',
		'manage_options',
		'foobot-api-page',
		'baindesign_foobot_options_page'
	);
}

function baindesign_settings_link($links)
{
	// Build and escape the URL.
	$url = esc_url(add_query_arg(
		'page',
		'foobot-api-page',
		get_admin_url() . 'options-general.php'
	));
	// Create the link.
	$settings_link = "<a href='$url'>" . __('Settings') . '</a>';
	// Adds the link to the end of the array.
	array_push(
		$links,
		$settings_link
	);
	return $links;
}
add_filter('plugin_action_links_wp-foobot-api/wp-foobot-api.php', 'baindesign_settings_link');

function baindesign_foobot_settings_init()
{
	// Register
	register_setting(
		'baindesignFoobot', 							// New options group
		'baindesign_foobot_api_settings'			// Entry in options table
	);

	add_settings_section(
		'baindesign-foobot-api-creds',						// Section ID
		__('Foobot API Credentials', 'aq-data-foobot'),	// Section header
		'baindesign_foobot_settings_section_callback',	// Callback
		'baindesignFoobot' 										// Add section to 
		// options group
	);

	// API Key
	add_settings_field(
		'baindesign_foobot_api_key',						// ID
		__('API Key', 'aq-data-foobot'),					// Label
		'baindesign_foobot_api_key_field_render',		// Function to display
		// inputs
		'baindesignFoobot',									// Page to display on
		'baindesign-foobot-api-creds'						// Section ID
	);

	// API username
	add_settings_field(
		'baindesign_foobot_api_user',						// ID
		__('API User', 'aq-data-foobot'),				// Label
		'baindesign_foobot_api_user_field_render',	// Function to
		// display inputs
		'baindesignFoobot',									// Page to display on
		'baindesign-foobot-api-creds'						// Section ID
	);
}

function baindesign_foobot_api_user_field_render()
{
	$options = get_option('baindesign_foobot_api_settings');
?>
	<input type='email' name='baindesign_foobot_api_settings[baindesign_foobot_api_user]' placeholder='<?php _e("Your API email", 'aq-data-foobot'); ?>' value='<?php echo esc_html($options['baindesign_foobot_api_user']); ?>'>
<?php
}

function baindesign_foobot_api_key_field_render()
{
	$options = get_option('baindesign_foobot_api_settings');
?>
	<textarea rows="7" cols="50" name='baindesign_foobot_api_settings[baindesign_foobot_api_key]' placeholder='<?php _e("Your API key", 'aq-data-foobot'); ?>'><?php echo esc_textarea($options['baindesign_foobot_api_key']); ?></textarea>
<?php
}
/**
 * Render the settings section content
 */
function baindesign_foobot_settings_section_callback()
{
	_e('Add your Foobot API credentials below. An API key can be obtained at <a href="https://api.foobot.io/apidoc/index.html">api.foobot.io</a>.', 'aq-data-foobot');
}

/**
 * Render the options page form
 */

function baindesign_foobot_options_page()
{
?>
	<div class="wrap">
		<form action='options.php' method='post'>

			<h1><?php _e("Foobot API Admin Page", 'aq-data-foobot'); ?></h1>
			<?php
			settings_fields('baindesignFoobot');
			do_settings_sections('baindesignFoobot');
			submit_button();
			?>

		</form>
	</div>
<?php
}
