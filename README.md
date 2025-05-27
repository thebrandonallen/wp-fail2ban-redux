# WP Fail2Ban Redux #
**Contributors:** [thebrandonallen](https://profiles.wordpress.org/thebrandonallen/)  
**Donate link:** https://brandonallen.me/donate/  
**Tags:** fail2ban, login, security, syslog  
**Requires at least:** 5.5  
**Tested up to:** 6.4  
**Requires PHP:** 7.0  
**Stable tag:** 0.9.1  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/old-licenses/gpl-2.0.html  

Records various WordPress events to your server's system log for integration with Fail2Ban.

## Description ##

WP Fail2Ban Redux records various WordPress events to your server's system log for integration with [Fail2Ban](http://www.fail2ban.org/).

This plugin is (*mostly*) a drop-in replacement for [WP fail2ban](https://wordpress.org/plugins/wp-fail2ban/) by [Charles Lecklider](https://charles.lecklider.org/).

While WP fail2ban is a great plugin, there are a number of improvements that could be made. In order to facilitate these improvements, a major refactoring of the codebase was necessary.

The core functionality between *WP Fail2Ban Redux* and WP fail2ban remains the same. *WP Fail2Ban Redux* is considered to be *mostly* a drop-in replacement, because all constants have been replaced with filters, and will, possibly, require some upgrade work. Don’t work it’s as simple as implementing the constants.

**The following events are recorded by default:**

* Failed XML-RPC authentication attempts.
* Successful authentication attempts.
* Failed authentication attempts -- differentiated by a user's existence.
* Pingback errors.

**The following events can be enabled via filter:**

* Pingback requests.
* Blocked user enumeration attempts.
* Authentication attempts for blocked usernames.
* Spammed comments.

Extra documentation is available on the [WP Fail2Ban Redux GitHub Wiki](https://github.com/thebrandonallen/wp-fail2ban-redux/wiki).

## Installation ##

1. Upload the plugin to your plugins directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Copy the `config/filters/wordpress-hard.conf` and `config/filters/wordpress-soft.conf` files to your Fail2Ban filters directory (generally `/etc/fail2ban/filters.d`).
1. Copy the `config/jail/wordpress.conf` file to your Fail2Ban jail directory (generally `/etc/fail2ban/jail.d`), or append it's contents to your `jail.local` file. ***Make sure you read the notes in this file to aid successful setup.***
1. Reload or restart Fail2Ban.

## Frequently Asked Questions ##

### How do I upgrade from WP fail2ban? ###
If you haven't set any of the WP fail2ban constants, you don't need to do anything. If you have set some of the constants, [view the upgrade instructions](https://github.com/thebrandonallen/wp-fail2ban-redux/wiki/Upgrading-from-WP-fail2ban).

### Will the `wordpress-hard.conf` and `wordpress-soft.conf` filters still work? ###
Yes! All of the improvements made in *WP Fail2Ban Redux* were done in a way that would allow existing functionality to work without changes to your filters. However, the demo filters included with the plugin do contain some recommended changes. There are also new features not found in WP fail2ban that will require changes to your filters to be effective. These changes are linked, by filter, below:
[wordpress-hard.conf](https://github.com/thebrandonallen/wp-fail2ban-redux/compare/e3ec3c9...master#diff-3f035b688aa51aa342856e0efe9e5fb35628c3919dd60237c64cbbb9e337a4c7)
[wordpress-soft.conf](https://github.com/thebrandonallen/wp-fail2ban-redux/compare/e3ec3c9...master#diff-ab6c3eee8b8a798511f7315fcbc84eb594f13cf59fca287d2791dffb6e6d5e05)

*Be ye forewarned: Future changes to WP fail2ban may break backwards compatibility with WP Fail2Ban Redux filters. No attempts will be made to fix this. So, even though it's not required, it is probably a good idea to update the filters anyway.*

### Can I use this as a must-use plugin in the `mu-plugins` folder? ###
As of version 0.5.0, yes! Download the plugin, and unzip. Inside the plugin folder will be another folder named `wp-fail2ban-redux` and `wp-fail2ban-redux.php`. Upload this folder and file to the `mu-plugins` directory of your site.

### How do you I use this plugin if my site is behind a proxy, like Cloudflare? ###
You need to add some code to your `wp-config.php` file. See the below links for guidance.

* https://core.trac.wordpress.org/ticket/9235#comment:39
* https://stackoverflow.com/questions/14985518/cloudflare-and-logging-visitor-ip-addresses-via-in-php/14985633#14985633
* https://support.cloudflare.com/hc/en-us/articles/200170916#12345680

## Changelog ##

### 0.9.2 ###
* Release date: 2024-04-30
* Add a new regex rule for XMLRPC authentication failure to both filters (soft and hard)

### 0.9.1 ###
* Release date: 2023-10-17
* Bumps "Tested up to" version to 6.4
* Bumps minimum required PHP version to 7.0
* Bumps minimum required WP version to 5.5
* Update dependency package versions
* No changes to jail or filters in the release.

### 0.8.3 ###
* Release date: 2023-10-17
* Bumps "Tested up to" version to 5.9
* No changes to jail or filters in the release.

### 0.8.2 ###
* Release date: 2021-08-08
* Bumps "Tested up to" version to 5.8
* No changes to jail or filters in the release.

### 0.8.1 ###
* Release date: 2021-06-01
* Actually bumps "Tested up to" version to 5.7

### 0.8.0 ###
* Release date: 2021-05-31
* Bumps "Tested up to" version to 5.7
* Fix issue where logging out of WordPress could cause a blocked user log to be recorded

### 0.7.0 ###
* Release date: 2021-01-05
* Bumps "Tested up to" version to 5.6
* Move Composer dependencies to `require-dev` to reduce the number of packages installed when WP Fail2Ban Redux is installed via composer. See https://github.com/thebrandonallen/wp-fail2ban-redux/pull/17

### 0.6.0 ###
* Release date: 2020-06-07
* Bumps the minimum required version to WordPress 4.9.
* Bumps "Tested up to" version to 5.4.1

### 0.5.1 ###
* Release date: 2019-09-05
* **This release requires and update to the `wordpress-hard.conf` file, in order to fix an issue with matches failing for XML-RPC multicall authentication failures. See https://github.com/thebrandonallen/wp-fail2ban-redux/pull/13/commits/2e3a3867749be7839edfae5707b62921c36ecd85**
* Fix issue where XML-RPC multicall authentication failures weren't correctly matched by Fail2Ban with the `wordpress-hard.conf` filter.

### 0.5.0 ###
* Release date: 2018-10-27
* Add better support for use as a must-use plugin in the `mu-plugins` directory.

### 0.4.0 ###
* Release date: 2018-01-15
* Bumped the minimum required WordPress version to 4.5.
* Bumped the minimum required PHP version to 5.3. This is a soft bump, meaning, nothing changed that will break PHP 5.2 compatability. However, this could easily change in the future, and PHP 5.2 is no longer actively tested.
* Renamed the `wp_fail2ban_redux_openlog_indent` filter to `wp_fail2ban_redux_openlog_ident`, because... it was misspelled.

### 0.3.1 ###
* Release date: 2017-05-18
* Bump minimum required WordPress version to 4.4.
* Performance improvements when not blocking user enumeration.
* Use `wp_die` to exit, to allow for greater customization of exit messages.
* Exit messages are now escaped using `esc_html`.
* Refactored plugin loading.
* You can now create your own, custom, logging class, in case you don't want to use the standard `syslog()` output.

### 0.3.0 ###
* Superseded by 0.3.1

### 0.2.1 ###
* Release date: 2017-02-15
* Fix a stupid syntax error in the comment spam filter :( Thanks to @ichtarzan for reporting!

### 0.2.0 ###
* Release date: 2016-09-27
* Fixed PHP notices where `WP_Fail2Ban_Redux::comment_spam` expects two parameters. Decided it was probably a good idea to oblige.
* User enumeration blocking now checks for both the `author` and `author_name` parameters. The `author_name` parameter could be used to validate the existence of a particular username, so blocking on this parameter as well will further reduce the attack surface.
* Fixes an issue where user enumeration blocking was overzealous and would prevent actions in the admin area. Props [pjv](https://github.com/pjv). [#2](https://github.com/thebrandonallen/wp-fail2ban-redux/issues/2)
* `WP_Fail2Ban_Redux::redirect_canonical` is now deprecated. If you were doing anything with this function, or the hook that initialized it, you should look at `WP_Fail2Ban_Redux::user_enumeration` instead.
* Added a note to `wordpress.conf` about the `logpath` parameter, and common auth log locations. *There is no need to changed existing configurations.* This is merely to aid setup for future users.

### 0.1.1 ###
* Release date: 2016-07-23
* In PHP < 7.0, `exit` isn't allowed as a method name. `WP_Fail2Ban_Redux_Log::exit` is now `WP_Fail2Ban_Redux_Log::_exit`.

### 0.1.0 ###
* Release date: 2016-07-13
* Initial release.
