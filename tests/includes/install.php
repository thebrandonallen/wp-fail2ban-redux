<?php
/**
 * Installs WP Fail2Ban Redux for the purpose of the unit-tests
 *
 * @package WP_Fail2Ban_Redux
 */

// phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

$config_file_path = $argv[1];
$tests_dir_path   = $argv[2];

require_once $config_file_path;
require_once $tests_dir_path . '/includes/functions.php';

// phpcs:ignore Squiz.Commenting.FunctionComment.Missing
function _load_wp_fail2ban_redux() {
	require dirname( dirname( dirname( __FILE__ ) ) ) . '/wp-fail2ban-redux.php';
}
tests_add_filter( 'muplugins_loaded', '_load_wp_fail2ban_redux' );

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST']       = WP_TESTS_DOMAIN;
$PHP_SELF                   = '/index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$GLOBALS['PHP_SELF']        = $PHP_SELF; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$_SERVER['PHP_SELF']        = $PHP_SELF;

require_once ABSPATH . '/wp-settings.php';
