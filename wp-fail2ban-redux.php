<?php
/**
 * Plugin Name:       WP Fail2Ban Redux
 * Plugin URI:        https://github.com/thebrandonallen/wp-fail2ban-redux/
 * Description:       Records various WordPress events to your server's system log for integration with Fail2Ban.
 * Author:            Brandon Allen
 * Author URI:        https://github.com/thebrandonallen
 * Text Domain:       wp-fail2ban-redux
 * Domain Path:       /languages
 * Version:           0.9.2
 * Requires at least: 5.5
 * Requires PHP:      7.0
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * Copyright (C) 2012-2016  Charles Lecklider (email : wordpress@charles.lecklider.org)
 * Copyright (C) 2016-2023  Brandon Allen (https://github.com/thebrandonallen)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @package WP_Fail2Ban_Redux
 */

// Bail if accessed directly.
defined( 'ABSPATH' ) || exit;

// Include the WP Fail2Ban Redux classes.
require_once 'wp-fail2ban-redux/classes/class-wp-fail2ban-redux.php';
require_once 'wp-fail2ban-redux/classes/class-wp-fail2ban-redux-logger-interface.php';
require_once 'wp-fail2ban-redux/classes/class-wp-fail2ban-redux-logger.php';
require_once 'wp-fail2ban-redux/classes/class-wp-fail2ban-redux-log.php';

/**
 * Initialize WP Fail2Ban Redux.
 *
 * @since 0.3.0
 */
function wp_fail2ban_redux_init() {

	// Initialize the plugin.
	$wpf2br = WP_Fail2Ban_Redux::get_instance();
	$wpf2br->setup_actions();

	/**
	 * Fires after WP Fail2Ban Redux has been loaded and initialized.
	 *
	 * @since 0.3.0
	 */
	do_action( 'wp_fail2ban_redux_loaded' );
}
add_action( 'plugins_loaded', 'wp_fail2ban_redux_init' );
