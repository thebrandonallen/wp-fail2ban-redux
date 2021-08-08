<?php
/**
 * The WP Fail2Ban Redux Log class.
 *
 * @since 0.1.0
 *
 * @package WP_Fail2Ban_Redux
 * @subpackage Loggers
 */

// Bail if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Fail2Ban_Redux_Log' ) ) {

	/**
	 * The WP Fail2Ban Redux Log Class.
	 *
	 * @since 0.1.0
	 */
	class WP_Fail2Ban_Redux_Log {

		/**
		 * The remote IP address.
		 *
		 * @since 0.1.0
		 *
		 * @var string
		 */
		private static $ip;

		/**
		 * Calls PHP's `openlog()` function with our custom options.
		 *
		 * @since 0.1.0
		 * @deprecated 0.3.0
		 *
		 * @param string $action   The logging action.
		 * @param int    $facility The type of program logging the message.
		 *
		 * @return bool True on success.
		 */
		public static function openlog( $action = '', $facility = LOG_AUTH ) {
			_deprecated_function( __METHOD__, '0.3.0', 'WP_Fail2Ban_Redux_Logger::openlog()' );
			$logger = WP_Fail2Ban_Redux::get_instance()->get_logger();
			return $logger->openlog( $action, $facility );
		}

		/**
		 * Calls PHP's `syslog()` function with our custom options.
		 *
		 * @since 0.1.0
		 * @deprecated 0.3.0
		 *
		 * @param string $message  The log message with 'from {IP Address}' appended.
		 * @param int    $priority The message priority level.
		 * @param string $ip       The IP address.
		 *
		 * @return null|bool True on success. Null if no message passed. Else, false.
		 */
		public static function syslog( $message = '', $priority = LOG_NOTICE, $ip = '' ) {
			_deprecated_function( __METHOD__, '0.3.0', 'WP_Fail2Ban_Redux_Logger::syslog()' );
			$logger = WP_Fail2Ban_Redux::get_instance()->get_logger();
			return $logger->syslog( $message, $priority, $ip );
		}

		/**
		 * Ends script execution and returns a 403 status code.
		 *
		 * @since 0.1.0
		 * @since 0.1.1 Added underscore prefix, because `exit` is a reserved
		 *              name in PHP < 7.
		 * @deprecated 0.3.0
		 *
		 * @param string $action The logging action.
		 */
		public static function _exit( $action = '' ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
			_deprecated_function( __METHOD__, '0.3.0', 'WP_Fail2Ban_Redux_Logger::_exit()' );
			$logger = WP_Fail2Ban_Redux::get_instance()->get_logger();
			$logger->_exit( $action );
		}

		/**
		 * Returns the remote IP address of the current visitor.
		 *
		 * We use `REMOTE_ADDR` here directly. If you are behind a proxy, you
		 * should ensure that it is properly set, such as in wp-config.php, for
		 * your environment.
		 *
		 * @see https://core.trac.wordpress.org/ticket/9235#comment:39
		 *
		 * @since 0.1.0
		 *
		 * @return string The remote IP address.
		 */
		private static function get_remote_ip() {
			if ( empty( self::$ip ) ) {
				self::$ip = preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] );
			}

			return self::$ip;
		}
	}
} // End if.
