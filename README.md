# WP Fail2Ban Redux #
**Contributors:** thebrandonallen  
**Donate link:** https://brandonallen.me/donate/  
**Tags:** fail2ban, login, security, syslog  
**Requires at least:** 4.1.12  
**Tested up to:** 4.6  
**Stable tag:** 0.1.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Records various WordPress events to your server's system log for integration with Fail2Ban.

## Description ##

WP Fail2Ban Redux records various WordPress events to your server's system log for integration with [Fail2Ban](http://www.fail2ban.org/).

This plugin is (*mostly*) a drop-in replacement for [WP fail2ban](https://wordpress.org/plugins/wp-fail2ban/) by [Charles Lecklider](https://charles.lecklider.org/).

While WP fail2ban is a great plugin, there are a number of improvements that could be made. In order to facilitate these improvements, a major refactoring of the codebase was necessary.

The core functionality between *WP Fail2Ban Redux* and WP fail2ban remains the same. *WP Fail2Ban Redux* is considered to be *mostly* a drop-in replacement, because all constants have been replaced with filters. The biggest benefit to filters over constants is that they allow features to be more contextual.

The following events are recorded:

* Failed XML-RPC authentication attempts.
* Successful authentication attempts.
* Failed authentication attempts -- differentiated by a user's existence.
* Pingback errors.
* Pingback requests (*optional*).
* Blocked user enumeration attempts (*optional*).
* Authentication attempts for blocked user names (*optional*).
* Spammed comments (*optional*).

## Installation ##

1. Upload the plugin to your plugins directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Copy `wordpress-hard.conf` and `wordpress-soft.conf` to your Fail2Ban filters directory (generally `/etc/fail2ban/filters.d`).
1. Edit your `jail.local` to include something like:

		[wordpress-hard]
		enabled = true
		filter = wordpress-hard
		logpath = /var/log/auth.log
		maxretry = 2
		port = http,https

		[wordpress-soft]
		enabled = true
		filter = wordpress-soft
		logpath = /var/log/auth.log
		maxretry = 5
		port = http,https

1. Reload or restart Fail2Ban

## Frequently Asked Questions ##

### How do I upgrade from WP fail2ban? ###
If you haven't set any of the WP fail2ban constants, you don't need to do anything. If you have set some of the constants, upgrade instructions are below.

**`WP_FAIL2BAN_AUTH_LOG`**
This constant has been replaced by the `wp_fail2ban_redux_openlog_facility` filter. This filter is passed a second parameter with the action being taken.

**`WP_FAIL2BAN_SYSLOG_SHORT_TAG` and `WP_FAIL2BAN_HTTP_HOST`**
These two constants are used by WP fail2ban to create a tag for the system log (something like *wordpress(example.com)*). The short tag is now enforced, and two constants are merged into one filter, `wp_fail2ban_redux_openlog_indent`. This filter will receive a value like *wp(example.com)*.

**`WP_FAIL2BAN_PROXIES`**
There is no replacement for this constant. You should fix this in nginx/Apache, or by resetting the `$_SERVER['REMOTE_ADDR']` in your `wp-config.php`. See [https://core.trac.wordpress.org/ticket/9235](https://core.trac.wordpress.org/ticket/9235).

**`WP_FAIL2BAN_BLOCKED_USERS`**
This constant has been replaced by the `wp_fail2ban_redux_blocked_users` filter. This filter expects an array of usernames, rather than a regular expression pattern. Not only does this make it easier for a wider audience to use, it's also significantly faster than using a regular expression.

**`WP_FAIL2BAN_BLOCK_USER_ENUMERATION`**
This constant has been replace by the `wp_fail2ban_redux_block_user_enumeration` filter. Just like the constant it replaces, this filter expects a value of `true` or `false`, and defaults to `false`.

**`WP_FAIL2BAN_LOG_PINGBACKS`**
This constant has been replaced by the `wp_fail2ban_redux_log_pingbacks` filter. Just like the constant it replaces, this filter expects a value of `true` or `false`, and defaults to `false`.

There are also a number of other filters, with lots of documentation, available to make your wildest dreams come true.

### Will the `wordpress-hard.conf` and `wordpress-soft.conf` filters still work? ###
Yes, all of the improvements made in *WP Fail2Ban Redux* were done in a way that would allow these filters to continue to work without changes. There are some recommended changes which you can view [here](https://sweet.link/to/github/diff).

## Changelog ##

### 0.1.0 ###
* Initial release.
