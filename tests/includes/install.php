<?php
/**
 * Installs WP Fail2Ban Redux for the purpose of the unit-tests
 */
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

$config_file_path = $argv[1];
$tests_dir_path = $argv[2];

require_once $config_file_path;
require_once $tests_dir_path . '/includes/functions.php';

function _load_wp_fail2ban_redux() {
	require dirname( dirname( dirname( __FILE__ ) ) ) . '/wp-fail2ban-redux.php';
}
tests_add_filter( 'muplugins_loaded', '_load_wp_fail2ban_redux' );

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = WP_TESTS_DOMAIN;
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

require_once ABSPATH . '/wp-settings.php';
