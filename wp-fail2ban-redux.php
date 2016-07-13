<?php
/**
 * Plugin Name:     WP Fail2Ban Redux
 * Plugin URI:      https://github.com/thebrandonallen/wp-fail2ban-redux/
 * Description:     Records various WordPress events to your server's system log for integration with Fail2Ban.
 * Author:          Brandon Allen
 * Author URI:      https://github.com/thebrandonallen
 * Text Domain:     wp-fail2ban-redux
 * Domain Path:     /languages
 * Version:         0.1.0
 * License:         GPL2
 *
 * @package WP_Fail2Ban_Redux
 */

/**
 *  Copyright 2012-2016 Charles Lecklider (email : wordpress@charles.lecklider.org)
 *  Copyright 2016      Brandon Allen
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License, version 2, as
 *  published by the Free Software Foundation.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Bail if accessed directly.
defined( 'ABSPATH' ) || exit;

// Include the WP_Fail2Ban_Redux_Base class.
require_once 'classes/class-wp-fail2ban-redux-base.php';

if ( class_exists( 'WP_Fail2Ban_Redux_Base' ) ) {

	/**
	 * The main WP Fail2Ban Redux Class.
	 *
	 * Adds log messages to your system log in the below format:
	 * [TIMESTAMP] [SERVER HOSTNAME] [DAEMON/SERVICE][PID] [MESSAGE]
	 * Apr 1 14:12:34 hostname wp(example.com)[2003]: Accepted password for username from 192.168.1.1
	 *
	 * @since 0.1.0
	 */
	class WP_Fail2Ban_Redux extends WP_Fail2Ban_Redux_Base {

		/**
		 * The count of XML-RPC authentication failures.
		 *
		 * @since 0.1.0
		 *
		 * @var int
		 */
		private static $xmlrpc_failure_count = 0;

		/**
		 * Adds our actions and filters.
		 *
		 * @since 0.1.0
		 */
		protected function setup_actions() {

			// Filters.
			add_filter( 'authenticate', array( __CLASS__, 'authenticate' ), 1, 2 );
			add_filter( 'redirect_canonical', array( __CLASS__, 'redirect_canonical' ) );
			add_filter( 'xmlrpc_login_error', array( __CLASS__, 'xmlrpc_login_error' ), 1 );
			add_filter( 'xmlrpc_pingback_error', array( __CLASS__, 'xmlrpc_pingback_error' ), 1 );

			// Actions.
			add_action( 'comment_post', array( __CLASS__, 'comment_spam' ) );
			add_action( 'wp_login', array( __CLASS__, 'wp_login' ) );
			add_action( 'wp_login_failed', array( __CLASS__, 'wp_login_failed' ) );
			add_action( 'wp_set_comment_status', array( __CLASS__, 'comment_spam' ) );
			add_action( 'xmlrpc_call', array( __CLASS__, 'xmlrpc_call' ), 1 );
		}

		/* Filters ************************************************************/

		/**
		 * Checks for and logs attempts to authenticate as a blocked user.
		 *
		 * @since 0.1.0
		 *
		 * @param WP_User|WP_Error $user     The WP_User or WP_Error object.
		 * @param string           $username The username or email address.
		 *
		 * @return WP_User|WP_Error|void
		 */
		public static function authenticate( $user, $username ) {

			/**
			 * Filters the array of blocked users.
			 *
			 * @since 0.1.0
			 *
			 * @param array $users The array of usernames or email addresses.
			 */
			$users = (array) apply_filters( 'wp_fail2ban_redux_blocked_users', array() );

			// Log attempts to authenticate as a blocked user.
			if ( ! empty( $users ) ) {

				/**
				 * Filters the boolean of blocked users not in.
				 *
				 * The default is to block authetication attempts for any
				 * username in the blocked users array. If you'd rather block
				 * authentication attempts for users not in the blocked users
				 * array, return true on this filter.
				 *
				 * @since 0.1.0
				 *
				 * @param bool $not_in Defaults to false.
				 */
				$not_in = (bool) apply_filters( 'wp_fail2ban_redux_blocked_users_not_in', false );

				// Run the requested check.
				if ( $not_in ) {
					$blocked = ! in_array( $username, $users, true );
				} else {
					$blocked = in_array( $username, $users, true );
				}

				// If the username is blocked, log, and return a 403.
				if ( $blocked ) {
					self::openlog( 'authenticate' );
					self::syslog( "Blocked authentication attempt for {$username}" );
					self::exit( 'authenticate' );
				}
			}

			return $user;
		}

		/**
		 * Checks for, and logs, user enumeration attempts.
		 *
		 * Only enable this feature if you are using pretty permalinks,
		 * otherwise bad things will happen.
		 *
		 * @since 0.1.0
		 *
		 * @param WP_User|WP_Error $user     The WP_User or WP_Error object.
		 * @param string           $username The username or email address.
		 *
		 * @return WP_User|WP_Error|void
		 */
		public static function redirect_canonical( $redirect_url ) {

			/**
			 * Filters the user enumeration boolean.
			 *
			 * @since 0.1.0
			 *
			 * @param bool $enum Defaults to false.
			 */
			$enum = (bool) apply_filters( 'wp_fail2ban_redux_block_user_enumeration', false );

			// Maybe block and log user enumeration attempts.
			if ( $enum && isset( $_GET['author'] ) && (int) $_GET['author'] ) {
				self::openlog( 'redirect_canonical' );
				self::syslog( 'Blocked user enumeration attempt' );
				self::exit( 'redirect_canonical' );
			}

			return $redirect_url;
		}

		/**
		 * Logs XML-RPC authentication failures.
		 *
		 * @since 0.1.0
		 *
		 * @todo Maybe remove this from wordpress-hard.conf as it's actually
		 *       handled by the `authenticate`, `wp_login`, and
		 *       `wp_login_failed` hooks. Probably leave the notice as it helps
		 *       with auditing.
		 *
		 * @param IXR_Error $error The IXR_Error object.
		 *
		 * @return IXR_Error
		 */
		public static function xmlrpc_login_error( $error ) {

			// Log XML-RPC authentication failures.
			self::openlog( 'xmlrpc_login_error' );
			self::syslog( 'XML-RPC authentication failure' );

			// Bump the XML-RPC failure count.
			self::$xmlrpc_failure_count++;

			/*
			 * If the failure count is greater than 1, log the failure. Since
			 * the count is reset for each request, it can be reasonably assumed
			 * that it's the result of a multicall.
			 */
			if ( 1 < self::$xmlrpc_failure_count ) {
				self::syslog( 'XML-RPC multicall authentication failure' );
			}

			return $error;
		}

		/**
		 * Logs XML-RPC pingback errors.
		 *
		 * @since 0.1.0
		 *
		 * @param IXR_Error $error The IXR error object.
		 *
		 * @return IXR_Error
		 */
		public static function xmlrpc_pingback_error( $error ) {

			// Don't log a pingback error if a pingback was already registered.
			if ( 48 !== $error->code ) {
				self::openlog( 'xmlrpc_pingback_error' );
				self::syslog( "Pingback error {$error->code} generated" );
			}

			return $error;
		}

		/* Actions ************************************************************/

		/**
		 * Log spammed comments.
		 *
		 * @since 0.1.0
		 *
		 * @param int    $id     The comment id.
		 * @param string $status The comment status.
		 *
		 * @return void
		 */
		public static function comment_spam( $id, $status ) {

			/**
			 * Filters the log spam comments boolean.
			 *
			 * @since 0.1.0
			 *
			 * @param bool $comments Defaults to false.
			 */
			$comments = (bool) apply_filters( 'wp_fail2ban_redux_log_spam_comments', false );

			// Bail if we're not logging spam comments.
			if ( ! $comments ) {
				return;
			}

			// Bail if the comment isn't spam.
			if ( empty( 'spam' !== $status ) ) {
				return;
			}

			// Get the comment.
			$comment = get_comment( $id );
			if ( ! $comment ) {
				return;
			}

			self::openlog( 'comment_spam' );
			self::syslog( "Spammed comment", LOG_NOTICE, $comment->comment_author_IP );
		}

		/**
		 * Log successful authentication attempts.
		 *
		 * @since 0.1.0
		 *
		 * @param string $username The username.
		 */
		public static function wp_login( $username ) {
			self::openlog( 'wp_login' );
			self::syslog( "Accepted password for {$username}", LOG_INFO );
		}

		/**
		 * Log failed authentication attempts.
		 *
		 * @since 0.1.0
		 */
		public static function wp_login_failed( $username ) {

			// Use the cache to check that the user actually exists.
			$existing = '';
			if ( wp_cache_get( $username, 'userlogins' ) ) {
				$existing = $username;
			} elseif ( wp_cache_get( $username, 'useremail' ) ) {
				$existing = wp_cache_get( wp_cache_get( $username, 'useremail' ), 'users' )->user_login;
			}

			// Set our message variable based on the user's existence.
			$message = empty( $existing )
					 ? "Authentication attempt for unknown user {$username}"
					 : "Authentication failure for {$existing}";

			self::openlog( 'wp_login_failed' );
			self::syslog( $message );
		}

		/**
		 * Maybe log pingback requests.
		 *
		 * @since 0.1.0
		 *
		 * @todo Add more information to the log message like website, etc.
		 *
		 * @param string $name The method name.
		 *
		 * @return void
		 */
		public static function xmlrpc_call( $name ) {

			// Bail if we're not processing a pingback.
			if ( 'pingback.ping' !== $name ) {
				return;
			}

			/**
			 * Filters the log pingbacks boolean.
			 *
			 * @since 0.1.0
			 *
			 * @param bool $pingbacks Defaults to false.
			 */
			$pingbacks = (bool) apply_filters( 'wp_fail2ban_redux_log_pingbacks', false );

			// Maybe log pingback requests.
			if ( $pingbacks ) {

				global $wp_xmlrpc_server;

				$args = array();
				if ( is_object( $wp_xmlrpc_server ) ) {
					$args = $wp_xmlrpc_server->message->params;
				}

				$to = 'unknown';
				if ( ! empty( $args[1] ) ) {
					$to = esc_url_raw( $args[1] );
				}

				self::openlog( 'xmlrpc_call_pingback', LOG_USER );
				self::syslog( "Pingback requested for '{$to}'", LOG_INFO );
			}
		}
	}

	// Initialize WP Fail2Ban Redux.
	new WP_Fail2Ban_Redux;
}
