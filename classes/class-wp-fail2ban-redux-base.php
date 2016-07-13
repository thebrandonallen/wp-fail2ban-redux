<?php
/**
 * The WP Fail2Ban Redux Base Class.
 *
 * Extend this class
 *
 * @since 0.1.0
 *
 * @package WP_Fail2Ban_Redux
 * @subpackage WP_Fail2Ban_Redux_Base
 */

// Bail if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Fail2Ban_Redux_Base' ) ) {

	/**
	 * The WP Fail2Ban Redux Base Class.
	 *
	 * @since 0.1.0
	 * @abstract
	 */
	abstract class WP_Fail2Ban_Redux_Base {

		/**
		 * WP Fail2Ban Redux constructor.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			$this->setup_actions();
		}

		/**
		 * Adds our actions and filters.
		 *
		 * @since 0.1.0
		 * @abstract
		 */
		abstract protected function setup_actions();

		/**
		 * Calls PHP's `openlog()` function with our custom options.
		 *
		 * @since 0.1.0
		 *
		 * @param string $action   The logging action.
		 * @param int    $facility The type of program logging the message.
		 *
		 * @return bool True on success.
		 */
		protected static function openlog( $action = '', $facility = LOG_AUTH ) {

			/**
			 * Filters the $indent parameter, which will be the `[DAEMON]`
			 * portion of the example in the class PHPDoc, and will be passed to
			 * `openlog()`.
			 *
			 * See {@link https://secure.php.net/manual/function.openlog.php}
			 *
			 * @since 0.1.0
			 *
			 * @param string $indent The syslog tag.
			 * @param string $action The logging action.
			 */
			$indent = apply_filters( 'wp_fail2ban_redux_openlog_indent', "wp({$_SERVER['HTTP_HOST']})", $action );

			/**
			 * Filters the $option parameter, which is used to pass logging
			 * options to `openlog()`.
			 *
			 * See {@link https://secure.php.net/manual/function.openlog.php}
			 *
			 * @since 0.1.0
			 *
			 * @param int    $option The syslog options.
			 * @param string $action The logging action.
			 */
			$option = apply_filters( 'wp_fail2ban_redux_openlog_option', LOG_NDELAY|LOG_PID, $action );

			/**
			 * Filters the $facility parameter, which is used to tell `openlog()`
			 * the type of program logging the message.
			 *
			 * See {@link https://secure.php.net/manual/function.openlog.php}
			 *
			 * @since 0.1.0
			 *
			 * @param int    $facility The type of program logging the message.
			 * @param string $action   The logging action.
			 */
			$facility = apply_filters( 'wp_fail2ban_redux_openlog_facility', $facility, $action );

			return openlog( $indent, $option, $facility );
		}

		/**
		 * Calls PHP's `syslog()` function with our custom options.
		 *
		 * @since 0.1.0
		 *
		 * @param string $message  The log message with 'from {IP Address}' appended.
		 * @param int    $priority The message priority level.
		 * @param string $ip       The IP address.
		 *
		 * @return null|bool True on success. Null if no message passed. Else, false.
		 */
		protected static function syslog( $message = '', $priority = LOG_NOTICE, $ip = '' ) {

			// Don't log a message is none was passed.
			if  ( ! empty( $message ) ) {

				/**
				 * Filters the $priority parameter, which is used to tell
				 * `syslog()` the message priority level.
				 *
				 * See {@link https://secure.php.net/manual/function.syslog.php}
				 *
				 * @since 0.1.0
				 *
				 * @param int    $priority The message priority level.
				 * @param string $message  The log message with 'from {IP Address}' appended.
				 */
				$priority = apply_filters( 'wp_fail2ban_redux_syslog_priority', $priority, $message );

				// Get the remote IP address if none was passed.
				if ( empty( $ip ) ) {
					$ip = self::get_remote_ip();
				}

				return syslog( $priority, "{$message} from {$ip}" );
			}

			return null;
		}

		/**
		* Ends script execution and returns a 403 status code.
		*
		* @since 0.1.0
		*
		* @param string $action The logging action.
		*/
		protected static function exit( $action = '' ) {

			/**
			 * Fires before the script is exited and a 403 status is returned.
			 *
			 * @since 0.1.0
			 *
			 * @param string $action The logging action.
			 */
			do_action( 'wp_fail2ban_redux_exit', $action );

			/**
			 * Filters the exit message.
			 *
			 * @since 0.1.0
			 *
			 * @param string|int $message The exit message.
			 * @param string     $action  The logging action.
			 */
			$message = apply_filters( 'wp_fail2ban_redux_exit_message', 'Forbidden.', $action );

			ob_end_clean();
			header( 'HTTP/1.1 403 Forbidden' );
			header( 'Content-Type: text/plain' );
			exit( $message );
		}

		/**
		 * Returns the remote IP address of the current visitor.
		 *
		 * We use `REMOTE_ADDR` here directly. If you are behind a proxy, you
		 * should ensure that it is properly set, such as in wp-config.php, for
		 * your environment.
		 *
		 * See {@link https://core.trac.wordpress.org/ticket/9235}
		 *
		 * @since 0.1.0
		 *
		 * @return string The remote IP address.
		 */
		protected static function get_remote_ip() {
			return preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] );
		}
	}
}
