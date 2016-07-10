=== WP Fail2Ban Redux ===
Contributors: thebrandonallen
Donate link: https://brandonallen.me/donate/
Tags: fail2ban, login, security, syslog
Requires at least: 4.1.12
Tested up to: 4.6
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Records various WordPress events to your server's system log for integration with Fail2Ban.

== Description ==

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

== Installation ==

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

== Frequently Asked Questions ==

= How do I upgrade from WP fail2ban? =
If you haven't set any of the WP fail2ban constants, you don't need to do anything. If you have set some of the constants, [view the upgrade instructions](https://github.com/thebrandonallen/wp-fail2ban-redux/wiki/Upgrading-from-WP-fail2ban).

= Will the `wordpress-hard.conf` and `wordpress-soft.conf` filters still work? =
Yes! All of the improvements made in *WP Fail2Ban Redux* were done in a way that would allow existing functionality to work without changes to your filters. However, the demo filters included with the plugin do contain some recommended changes. There are also new features not found in WP fail2ban that will require changes to your filters to be effective. These changes are linked, by filter, below:
[wordpress-hard.conf](https://github.com/thebrandonallen/wp-fail2ban-redux/compare/e3ec3c9...master#diff-03e39c06976d40fc41208c0ff448babd)
[wordpress-soft.conf](https://github.com/thebrandonallen/wp-fail2ban-redux/compare/e3ec3c9...master#diff-4f0afadcecac37d4c1b48730e5ca848c)

== Changelog ==

= 0.1.0 =
* Initial release.
