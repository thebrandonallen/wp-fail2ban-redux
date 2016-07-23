<?php
/**
 * Plugin Name:     WP Fail2Ban Redux
 * Plugin URI:      https://github.com/thebrandonallen/wp-fail2ban-redux/
 * Description:     Records various WordPress events to your server's system log for integration with Fail2Ban.
 * Author:          Brandon Allen
 * Author URI:      https://github.com/thebrandonallen
 * Text Domain:     wp-fail2ban-redux
 * Domain Path:     /languages
 * Version:         0.1.1
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

// Include the WP Fail2Ban Redux classes.
require 'classes/class-wp-fail2ban-redux-log.php';
require 'classes/class-wp-fail2ban-redux.php';

// Initialize the plugin.
WP_Fail2Ban_Redux::get_instance();
