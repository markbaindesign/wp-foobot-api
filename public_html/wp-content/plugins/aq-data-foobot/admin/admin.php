<?php

/**
 * Admin screens
 */

add_action( 'admin_menu', 'baindesign_foobot_add_admin_menu' );
add_action( 'admin_init', 'baindesign_foobot_settings_init' );
add_action( 'admin_bar_menu', 'baindesign_foobot_admin_bar_menu', 999 );

function baindesign_foobot_admin_bar_menu( $wp_admin_bar ) {
	if ( ! BD0019__ADMIN_BAR_ENABLED || ! BD0019__ADMIN_BAR_DEVICE || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$uuid = bd_get_foobot_device_uuid( BD0019__ADMIN_BAR_DEVICE );

	if ( ! $uuid || 'error_device_not_found' === $uuid ) {
		return;
	}

	$sensor_data = bd_foobot_fetch_db_sensors( $uuid );

	if ( empty( $sensor_data ) || ! isset( $sensor_data[0]['datapointTmp'] ) ) {
		return;
	}

	$data = $sensor_data[0];

	$wp_admin_bar->add_node(
		array(
			'id'    => 'baindesign-foobot',
			'title' => esc_html(
				sprintf(
					'%s: %s%s',
					BD0019__ADMIN_BAR_DEVICE,
					number_format( $data['datapointTmp'], 1 ),
					$data['unitTmp']
				)
			),
			'href'  => admin_url( 'options-general.php?page=aq-data-foobot' ),
		)
	);
}

function baindesign_foobot_add_admin_menu() {
	add_options_page(
		'Air Quality Data from Foobot',
		'Foobot API',
		'manage_options',
		'aq-data-foobot',
		'baindesign_foobot_options_page'
	);
}

function baindesign_settings_link( $links ) {
	// Build and escape the URL.
	$url = esc_url(
		add_query_arg(
			'page',
			'aq-data-foobot',
			get_admin_url() . 'options-general.php'
		)
	);
	// Create the link.
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	// Adds the link to the end of the array.
	array_push(
		$links,
		$settings_link
	);
	return $links;
}
add_filter( 'plugin_action_links_aq-data-foobot/aq-data-foobot.php', 'baindesign_settings_link' );

function baindesign_foobot_sanitize_settings( $input ) {
	$output   = array();
	$existing = get_option( BD0019__API_OPTIONS_NAME, array() );

	// An empty submitted key means "keep the current key" (the field is
	// left blank/hidden in the UI so the stored key isn't shown on screen).
	if ( ! empty( $input[ BD0019__API_KEY_FIELD ] ) ) {
		$output[ BD0019__API_KEY_FIELD ] = sanitize_textarea_field( $input[ BD0019__API_KEY_FIELD ] );
	} elseif ( ! empty( $existing[ BD0019__API_KEY_FIELD ] ) ) {
		$output[ BD0019__API_KEY_FIELD ] = $existing[ BD0019__API_KEY_FIELD ];
	}

	if ( isset( $input[ BD0019__API_USER_FIELD ] ) ) {
		$output[ BD0019__API_USER_FIELD ] = sanitize_text_field( $input[ BD0019__API_USER_FIELD ] );
	}

	$output[ BD0019__ADMIN_BAR_ENABLED_FIELD ] = ! empty( $input[ BD0019__ADMIN_BAR_ENABLED_FIELD ] ) ? 1 : 0;

	if ( isset( $input[ BD0019__ADMIN_BAR_DEVICE_FIELD ] ) ) {
		$output[ BD0019__ADMIN_BAR_DEVICE_FIELD ] = sanitize_text_field( $input[ BD0019__ADMIN_BAR_DEVICE_FIELD ] );
	}

	return $output;
}

function baindesign_foobot_settings_init() {
	// Register
	register_setting(
		'baindesignFoobot',                             // New options group
		BD0019__API_OPTIONS_NAME,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'baindesign_foobot_sanitize_settings',
			'default'           => array(),
		)
	);

	add_settings_section(
		'baindesign-foobot-api-creds',                      // Section ID
		__( 'Foobot API Credentials', 'aq-data-foobot' ), // Section header
		'baindesign_foobot_settings_section_callback',  // Callback
		'baindesignFoobot'                                      // Add section to
		// options group
	);

	// API Key
	add_settings_field(
		BD0019__API_KEY_FIELD,                      // ID
		__( 'API Key', 'aq-data-foobot' ),                    // Label
		BD0019__API_KEY_FIELD . '_field_render',        // Function to display
		// inputs
		'baindesignFoobot',                                 // Page to display on
		'baindesign-foobot-api-creds'                       // Section ID
	);

	// API username
	add_settings_field(
		BD0019__API_USER_FIELD,                     // ID
		__( 'API User', 'aq-data-foobot' ),               // Label
		BD0019__API_USER_FIELD . '_field_render',   // Function to
		// display inputs
		'baindesignFoobot',                                 // Page to display on
		'baindesign-foobot-api-creds'                       // Section ID
	);

	add_settings_section(
		'baindesign-foobot-admin-bar',                       // Section ID
		__( 'Admin Bar', 'aq-data-foobot' ),                // Section header
		'baindesign_foobot_admin_bar_section_callback',     // Callback
		'baindesignFoobot'                                      // Add section to
		// options group
	);

	// Show in admin bar
	add_settings_field(
		BD0019__ADMIN_BAR_ENABLED_FIELD,
		__( 'Show in Admin Bar', 'aq-data-foobot' ),
		BD0019__ADMIN_BAR_ENABLED_FIELD . '_field_render',
		'baindesignFoobot',
		'baindesign-foobot-admin-bar'
	);

	// Which device to show
	add_settings_field(
		BD0019__ADMIN_BAR_DEVICE_FIELD,
		__( 'Device', 'aq-data-foobot' ),
		BD0019__ADMIN_BAR_DEVICE_FIELD . '_field_render',
		'baindesignFoobot',
		'baindesign-foobot-admin-bar'
	);
}

function baindesign_foobot_api_user_field_render() {
	?>
	<input type='email' name='baindesign_foobot_api_settings[baindesign_foobot_api_user]' placeholder='<?php _e( 'Your API email', 'aq-data-foobot' ); ?>' value='<?php echo esc_html( BD0019__API_USER ); ?>'>
	<?php
}

function baindesign_foobot_api_key_field_render() {
	if ( BD0019__API_KEY ) {
		$masked = str_repeat( '•', 8 ) . substr( BD0019__API_KEY, -4 );
		?>
		<p>
			<code><?php echo esc_html( $masked ); ?></code>
			<label style="display:block;margin-top:8px;font-weight:normal;">
				<input type="checkbox" onchange="document.getElementById('bdf-key-textarea').style.display = this.checked ? 'block' : 'none';">
				<?php esc_html_e( 'Change API Key', 'aq-data-foobot' ); ?>
			</label>
			<textarea id="bdf-key-textarea" rows="7" cols="50" name='baindesign_foobot_api_settings[baindesign_foobot_api_key]' placeholder='<?php echo esc_attr__( 'Enter a new API key', 'aq-data-foobot' ); ?>' style="display:none;margin-top:8px;"></textarea>
		</p>
		<?php
	} else {
		?>
		<textarea rows="7" cols="50" name='baindesign_foobot_api_settings[baindesign_foobot_api_key]' placeholder='<?php echo esc_attr__( 'Your API key', 'aq-data-foobot' ); ?>'></textarea>
		<?php
	}
}
/**
 * Render the settings section content
 */
function baindesign_foobot_settings_section_callback() {
	_e( 'Add your Foobot API credentials below. An API key can be obtained at <a href="https://api.foobot.io/apidoc/index.html">api.foobot.io</a>.', 'aq-data-foobot' );
}

function baindesign_foobot_admin_bar_section_callback() {
	esc_html_e( 'Optionally show a device\'s latest reading in the WordPress admin bar.', 'aq-data-foobot' );
}

function baindesign_foobot_admin_bar_enabled_field_render() {
	?>
	<label>
		<input type="checkbox" name="baindesign_foobot_api_settings[baindesign_foobot_admin_bar_enabled]" value="1" <?php checked( BD0019__ADMIN_BAR_ENABLED ); ?>>
		<?php esc_html_e( 'Show data in the admin bar', 'aq-data-foobot' ); ?>
	</label>
	<?php
}

function baindesign_foobot_admin_bar_device_field_render() {
	$devices = ( BD0019__API_KEY && BD0019__API_USER ) ? bd_foobot_fetch_db_devices() : array();

	if ( empty( $devices ) ) {
		esc_html_e( 'No devices found yet -- save your API credentials first.', 'aq-data-foobot' );
		return;
	}
	?>
	<select name="baindesign_foobot_api_settings[baindesign_foobot_admin_bar_device]">
		<?php foreach ( $devices as $device ) : ?>
			<option value="<?php echo esc_attr( $device['name'] ); ?>" <?php selected( BD0019__ADMIN_BAR_DEVICE, $device['name'] ); ?>>
				<?php echo esc_html( $device['name'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}

/**
 * Handle the "Test Connection" button, if submitted.
 *
 * @return array|null {success: bool, message: string} or null if not submitted.
 */
function baindesign_foobot_handle_test_connection() {
	if ( ! isset( $_POST['baindesign_foobot_test_connection'] ) ) {
		return null;
	}

	check_admin_referer( 'baindesign_foobot_test_connection' );

	if ( ! current_user_can( 'manage_options' ) ) {
		return null;
	}

	if ( ! BD0019__API_KEY || ! BD0019__API_USER ) {
		return array(
			'success' => false,
			'message' => __( 'Please save your API key and API user above before testing the connection.', 'aq-data-foobot' ),
		);
	}

	$result = bd_foobot_call_api_devices();

	if ( is_array( $result ) && isset( $result['state'] ) ) {
		return array(
			'success' => false,
			'message' => sprintf(
				/* translators: %d: HTTP status code returned by the Foobot API */
				__( 'Connection failed: the Foobot API returned an error (status %d). Double-check your API key and API user email.', 'aq-data-foobot' ),
				(int) $result['state']
			),
		);
	}

	if ( is_array( $result ) ) {
		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: number of devices found */
				_n( 'Connection successful: found %d device.', 'Connection successful: found %d devices.', count( $result ), 'aq-data-foobot' ),
				count( $result )
			),
		);
	}

	return array(
		'success' => false,
		'message' => __( 'Connection failed: no response from the Foobot API.', 'aq-data-foobot' ),
	);
}

/**
 * Get a human-readable "last data received" string, or a fallback message.
 */
function baindesign_foobot_get_last_received_text() {
	$data = bd_foobot_fetch_latest_sensor_data();

	if ( empty( $data['timestamp'] ) ) {
		return __( 'No data received yet.', 'aq-data-foobot' );
	}

	return sprintf(
		/* translators: %s: date/time the most recent sensor reading was received */
		__( 'Last data received: %s', 'aq-data-foobot' ),
		bd324_get_local_datetime( $data['timestamp'] )
	);
}

/**
 * Render the list of devices found on the account
 */
function baindesign_foobot_render_devices_list() {
	if ( ! BD0019__API_KEY || ! BD0019__API_USER ) {
		echo '<p>' . esc_html__( 'Save your API credentials above to see the devices found on your Foobot account.', 'aq-data-foobot' ) . '</p>';
		return;
	}

	$devices = bd_foobot_fetch_db_devices();

	if ( empty( $devices ) ) {
		echo '<p>' . esc_html__( 'No devices found on this Foobot account.', 'aq-data-foobot' ) . '</p>';
		return;
	}
	?>
	<table class="widefat striped" style="max-width: 600px;">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Device Name', 'aq-data-foobot' ); ?></th>
				<th><?php esc_html_e( 'Shortcode', 'aq-data-foobot' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $devices as $device ) : ?>
				<tr>
					<td><?php echo esc_html( $device['name'] ); ?></td>
					<td><code>[foobot-show-data device="<?php echo esc_attr( $device['name'] ); ?>"]</code></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Render the options page form
 */

function baindesign_foobot_options_page() {
	$test_result = baindesign_foobot_handle_test_connection();
	?>
	<div class="wrap">
		<h1><?php _e( 'Air Quality Data from Foobot', 'aq-data-foobot' ); ?></h1>

		<?php if ( $test_result ) : ?>
			<div class="notice <?php echo $test_result['success'] ? 'notice-success' : 'notice-error'; ?> is-dismissible">
				<p><?php echo esc_html( $test_result['message'] ); ?></p>
			</div>
		<?php endif; ?>

		<form action='options.php' method='post'>
			<?php
			settings_fields( 'baindesignFoobot' );
			do_settings_sections( 'baindesignFoobot' );
			submit_button();
			?>
		</form>

		<form method="post" style="display:flex;align-items:center;gap:12px;">
			<?php wp_nonce_field( 'baindesign_foobot_test_connection' ); ?>
			<input type="hidden" name="baindesign_foobot_test_connection" value="1">
			<?php submit_button( __( 'Test Connection', 'aq-data-foobot' ), 'secondary', 'submit', false ); ?>
			<span><?php echo esc_html( baindesign_foobot_get_last_received_text() ); ?></span>
		</form>

		<h2><?php esc_html_e( 'Devices Found', 'aq-data-foobot' ); ?></h2>
		<?php baindesign_foobot_render_devices_list(); ?>

	</div>
	<?php
}
