<?php
/**
 * Bootstrap the plugin unit testing environment.
 *
 * @package WordPress
 * @subpackage WP_Fail2Ban_Redux
 */

require_once dirname( dirname( __FILE__ ) ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

if ( defined( 'WP_FAIL2BAN_REDUX_WP_ENV_TESTS' ) ) {
	// wp-env setup.
	define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __FILE__ ) . '/assets/phpunit-wp-config.php' );
	define( 'WP_TESTS_CONFIG_PATH', WP_TESTS_CONFIG_FILE_PATH );
	// Use WP PHPUnit.
	require_once dirname( dirname( __FILE__ ) ) . '/vendor/wp-phpunit/wp-phpunit/__loaded.php';
}

require dirname( __FILE__ ) . '/includes/define-constants.php';

if ( ! file_exists( WP_TESTS_DIR . '/includes/functions.php' ) ) {
	die( "The WordPress PHPUnit test suite could not be found.\n" );
}

require_once WP_TESTS_DIR . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require WP_FAIL2BAN_REDUX_TESTS_DIR . '/includes/loader.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require WP_TESTS_DIR . '/includes/bootstrap.php';
