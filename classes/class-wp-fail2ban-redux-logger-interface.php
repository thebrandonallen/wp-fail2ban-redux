<?php
/**
 * The WP Fail2Ban Redux Logger Interface.
 *
 * @since 0.3.0
 *
 * @package WP_Fail2Ban_Redux
 * @subpackage Loggers
 */

// Bail if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The WP Fail2Ban Redux Logger Interface.
 *
 * @since 0.3.0
 */
interface WP_Fail2Ban_Redux_Logger_Interface {

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
	public function openlog( $action = '', $facility = LOG_AUTH );

	/**
	 * Calls PHP's `syslog()` function with our custom options.
	 *
	 * @since 0.3.0
	 *
	 * @param string $message  The log message with 'from {IP Address}' appended.
	 * @param int    $priority The message priority level.
	 * @param string $ip       The IP address.
	 *
	 * @return null|bool True on success. Null if no message passed.
	 */
	public function syslog( $message = '', $priority = LOG_NOTICE, $ip = '' );

	/**
	 * Ends script execution and returns a 403 status code.
	 *
	 * @since 0.3.0
	 *
	 * @param string $action The logging action.
	 *
	 * @return void
	 */
	public function _exit( $action = '' );
}
