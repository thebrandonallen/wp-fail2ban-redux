<?php
/**
 * The WP Fail2Ban Redux Logger class.
 *
 * @since 0.3.0
 *
 * @package WP_Fail2Ban_Redux
 * @subpackage WP_Fail2Ban_Redux_Logger
 */

// Bail if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The WP Fail2Ban Redux Log Class.
 *
 * @since 0.3.0
 */
class WP_Fail2Ban_Redux_Logger_Mock extends WP_Fail2Ban_Redux_Logger {

	/**
	 * Calls PHP's `openlog()` function with our custom options.
	 *
	 * @since 0.3.0
	 *
	 * @param string $action   The logging action.
	 * @param int    $facility The type of program logging the message.
	 *
	 * @return bool True on success.
	 */
	public function openlog( $action = '', $facility = LOG_AUTH ) {
		echo 'openlog:' . $action; // WPCS: XSS okay.
	}

	/**
	 * Calls PHP's `syslog()` function with our custom options.
	 *
	 * @since 0.3.0
	 *
	 * @param string $message  The log message with 'from {IP Address}' appended.
	 * @param int    $priority The message priority level.
	 * @param string $ip       The IP address.
	 *
	 * @return null|bool True on success. Null if no message passed. Else, false.
	 */
	public function syslog( $message = '', $priority = LOG_NOTICE, $ip = '' ) {
		echo ':syslog:' . $message; // WPCS: XSS okay.
	}

	/**
	 * Ends script execution and returns a 403 status code.
	 *
	 * @since 0.3.0
	 *
	 * @param string $action The logging action.
	 */
	public function _exit( $action = '' ) {
		echo ':exit:' . $action; // WPCS: XSS okay.
	}
}
