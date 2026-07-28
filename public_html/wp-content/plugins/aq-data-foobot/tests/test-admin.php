<?php
/**
 * Tests for the admin settings screen in admin/admin.php.
 *
 * @package AqDataFoobot
 */

class Test_Admin extends WP_UnitTestCase {

	public function test_admin_menu_hook_registered() {
		$this->assertNotFalse( has_action( 'admin_menu', 'baindesign_foobot_add_admin_menu' ) );
	}

	public function test_admin_init_hook_registered() {
		$this->assertNotFalse( has_action( 'admin_init', 'baindesign_foobot_settings_init' ) );
	}

	public function test_settings_link_filter_registered() {
		$this->assertNotFalse(
			has_filter( 'plugin_action_links_aq-data-foobot/aq-data-foobot.php', 'baindesign_settings_link' )
		);
	}

	public function test_settings_link_appends_settings_url() {
		$links = baindesign_settings_link( array( 'deactivate' => '<a href="#">Deactivate</a>' ) );

		$this->assertCount( 2, $links );
		$this->assertStringContainsString( 'page=aq-data-foobot', end( $links ) );
	}
}
