<?php
/**
 * Tests for the device lookup helpers in includes/helpers.php.
 *
 * @package AqDataFoobot
 */

class Test_Helpers extends WP_UnitTestCase {

	/**
	 * Table writes commit outside the per-test transaction because dbDelta's
	 * CREATE TABLE is DDL, so rows are truncated manually between tests.
	 */
	public function set_up() {
		parent::set_up();
		bd_foobot_create_device_table();

		// Block any live HTTP call to the Foobot API. The transient guard in
		// bd_foobot_call_api_trans_devices() does `1 === get_transient(...)`,
		// which never matches (options are stored/read back as strings), so
		// every call falls through to a real wp_remote_get() unless we
		// short-circuit it here.
		add_filter( 'pre_http_request', array( $this, 'block_http_requests' ), 10, 3 );
	}

	public function tear_down() {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}bd_foobot_device_data" );
		remove_filter( 'pre_http_request', array( $this, 'block_http_requests' ), 10 );
		parent::tear_down();
	}

	public function block_http_requests( $preempt, $args, $url ) {
		return new WP_Error( 'blocked_in_tests', 'Live HTTP requests are blocked in the test suite: ' . $url );
	}

	private function insert_device( $name, $uuid ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'bd_foobot_device_data',
			array(
				'timestamp' => time(),
				'name'      => $name,
				'uuid'      => $uuid,
			),
			array( '%d', '%s', '%s' )
		);
	}

	public function test_get_device_uuid_returns_uuid_for_known_device() {
		$this->insert_device( 'Living Room', 'uuid-123' );
		$this->assertSame( 'uuid-123', bd_get_foobot_device_uuid( 'Living Room' ) );
	}

	public function test_get_device_uuid_returns_error_for_unknown_device() {
		$this->insert_device( 'Living Room', 'uuid-123' );
		$this->assertSame( 'error_device_not_found', bd_get_foobot_device_uuid( 'Bedroom' ) );
	}

	public function test_get_device_name_from_uuid_returns_name() {
		$this->insert_device( 'Living Room', 'uuid-123' );
		$this->assertSame( 'Living Room', bd324_get_device_name_from_uuid( 'uuid-123' ) );
	}

	public function test_get_device_name_from_uuid_returns_null_when_not_found() {
		$this->assertNull( bd324_get_device_name_from_uuid( 'does-not-exist' ) );
	}

	public function test_get_local_datetime_formats_utc_timestamp() {
		update_option( 'date_format', 'Y-m-d' );
		update_option( 'time_format', 'H:i' );

		$timestamp = strtotime( '2024-01-15 10:30:00 UTC' );
		$result    = bd324_get_local_datetime( $timestamp );

		$this->assertIsString( $result );
		$this->assertNotEmpty( $result );
	}
}
