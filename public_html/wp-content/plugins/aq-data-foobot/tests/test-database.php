<?php
/**
 * Tests for the API credential getters in includes/database.php.
 *
 * @package AqDataFoobot
 */

class Test_Database extends WP_UnitTestCase {

	/**
	 * BD0019__API_KEY / BD0019__API_USER are constants baked in from the
	 * option value at the moment the plugin file was first required (see
	 * tests/bootstrap.php), so these getters always see an empty string
	 * in this suite and fall back to their "not found" messages.
	 */
	public function test_get_api_key_returns_not_found_message_when_unconfigured() {
		$this->assertSame( 'API key not found', bd_foobot_get_api_key() );
	}

	public function test_get_api_user_returns_not_found_message_when_unconfigured() {
		$this->assertSame( 'API username not found', bd_foobot_get_api_user() );
	}
}
