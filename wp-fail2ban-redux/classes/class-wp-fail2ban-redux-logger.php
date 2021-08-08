<?php
/**
 * The WP Fail2Ban Redux Logger class.
 *
 * @since 0.3.0
 *
 * @package WP_Fail2Ban_Redux
 * @subpackage Loggers
 */

// Bail if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Fail2Ban_Redux_Logger' ) ) {

	/**
	 * The WP Fail2Ban Redux Logger Class.
	 *
	 * @since 0.3.0
	 */
	class WP_Fail2Ban_Redux_Logger implements WP_Fail2Ban_Redux_Logger_Interface {

		/**
		 * The remote IP address.
		 *
		 * @since 0.3.0
		 *
		 * @var string
		 */
		protected $ip;

		/**
		 * Calls PHP's `openlog()` function with our custom options.
		 *
		 * @see https://secure.php.net/manual/function.openlog.php
		 *
		 * @since 0.3.0
		 *
		 * @param string $action   The logging action.
		 * @param int    $facility The type of program logging the message.
		 *
		 * @return bool True on success.
		 */
		public function openlog( $action = '', $facility = LOG_AUTH ) {

			// Setup the initial `$ident` value.
			$ident = "wp({$_SERVER['HTTP_HOST']})";

			/**
			 * Filters the $ident parameter, which will be the `[DAEMON]`
			 * portion of the example in the class PHPDoc, and will be passed to
			 * `openlog()`.
			 *
			 * @see https://secure.php.net/manual/function.openlog.php
			 *
			 * @since 0.1.0
			 *
			 * @deprecated 0.4.0 See https://github.com/thebrandonallen/wp-fail2ban-redux/issues/7
			 *
			 * @param string $ident  The syslog tag.
			 * @param string $action The logging action.
			 */
			$ident = apply_filters_deprecated( 'wp_fail2ban_redux_openlog_indent', array( $ident, $action ), '0.4.0', 'wp_fail2ban_redux_openlog_ident' );

			/**
			 * Filters the $ident parameter, which will be the `[DAEMON]`
			 * portion of the example in the class PHPDoc, and will be passed to
			 * `openlog()`.
			 *
			 * @see https://secure.php.net/manual/function.openlog.php
			 *
			 * @since 0.4.0
			 *
			 * @param string $ident  The syslog tag.
			 * @param string $action The logging action.
			 */
			$ident = apply_filters( 'wp_fail2ban_redux_openlog_ident', $ident, $action );

			/**
			 * Filters the $option parameter, which is used to pass logging
			 * options to `openlog()`.
			 *
			 * @see https://secure.php.net/manual/function.openlog.php
			 *
			 * @since 0.1.0
			 *
			 * @param int    $option The syslog options.
			 * @param string $action The logging action.
			 */
			$option = apply_filters( 'wp_fail2ban_redux_openlog_option', LOG_NDELAY | LOG_PID, $action );

			/**
			 * Filters the $facility parameter, which is used to tell `openlog()`
			 * the type of program logging the message.
			 *
			 * @see https://secure.php.net/manual/function.openlog.php
			 *
			 * @since 0.1.0
			 *
			 * @param int    $facility The type of program logging the message.
			 * @param string $action   The logging action.
			 */
			$facility = apply_filters( 'wp_fail2ban_redux_openlog_facility', $facility, $action );

			return openlog( $ident, $option, $facility );
		}

		/**
		 * Calls PHP's `syslog()` function with our custom options.
		 *
		 * @see https://secure.php.net/manual/function.openlog.php
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

			// Don't log a message is none was passed.
			if ( ! empty( $message ) ) {

				/**
				 * Filters the $priority parameter, which is used to tell
				 * `syslog()` the message priority level.
				 *
				 * @see https://secure.php.net/manual/function.syslog.php
				 *
				 * @since 0.1.0
				 *
				 * @param int    $priority The message priority level.
				 * @param string $message  The log message with 'from {IP Address}' appended.
				 */
				$priority = apply_filters( 'wp_fail2ban_redux_syslog_priority', $priority, $message );

				// Get the remote IP address if none was passed.
				if ( empty( $ip ) ) {
					$ip = $this->get_remote_ip();
				}

				return syslog( $priority, "{$message} from {$ip}" );
			}

			return null;
		}

		/**
		 * Ends script execution and returns a 403 status code.
		 *
		 * @since 0.3.0
		 *
		 * @param string $action The logging action.
		 */
		public function _exit( $action = '' ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

			/**
			 * Fires before the script is exited and a 403 status is returned.
			 *
			 * @since 0.1.0
			 *
			 * @param string $action The logging action.
			 */
			do_action( 'wp_fail2ban_redux_exit', $action );

			/**
			 * Filters the `wp_die` message parameter.
			 *
			 * @since 0.1.0
			 *
			 * @param string|int $message The exit message.
			 * @param string     $action  The logging action.
			 */
			$message = apply_filters(
				'wp_fail2ban_redux_exit_message',
				__( 'Forbidden.', 'wp-fail2ban-redux' ),
				$action
			);

			/**
			 * Filters the `wp_die` title parameter.
			 *
			 * @since 0.3.0
			 *
			 * @param string|int $title  The `wp_die` title.
			 * @param string     $action The logging action.
			 */
			$title = apply_filters(
				'wp_fail2ban_redux_exit_title',
				__( 'Forbidden.', 'wp-fail2ban-redux' ),
				$action
			);

			/**
			 * Filters the `wp_die` args parameter.
			 *
			 * @since 0.3.0
			 *
			 * @param array|int $args   The `wp_die` args.
			 * @param string    $action The logging action.
			 */
			$args = apply_filters(
				'wp_fail2ban_redux_exit_wp_die_args',
				array(
					'response' => 403,
				),
				$action
			);

			wp_die( esc_html( $message ), esc_html( $title ), $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
		 * @since 0.3.0
		 *
		 * @return string The remote IP address.
		 */
		protected function get_remote_ip() {
			if ( empty( $this->ip ) ) {
				$this->ip = preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] );
			}

			return $this->ip;
		}
	}
} // End if.
