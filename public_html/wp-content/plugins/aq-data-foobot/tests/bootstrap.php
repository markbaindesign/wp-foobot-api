<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package AqDataFoobot
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 *
 * The plugin reads its API settings option into constants as soon as it's
 * included, so the option needs a default value in place first or the
 * array access on a `false` option emits a warning (which PHPUnit turns
 * into a fatal error under this bootstrap's error-to-exception config).
 */
function _manually_load_plugin() {
	if ( false === get_option( 'baindesign_foobot_api_settings' ) ) {
		update_option(
			'baindesign_foobot_api_settings',
			array(
				'baindesign_foobot_api_key'  => '',
				'baindesign_foobot_api_user' => '',
			)
		);
	}

	require dirname( __DIR__ ) . '/aq-data-foobot.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
