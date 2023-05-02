<?php

if ( ! function_exists( 'tests_get_phpunit_version' ) ) {
	/**
	 * Retrieves PHPUnit runner version.
	 */
	function tests_get_phpunit_version() {
		if ( class_exists( 'PHPUnit_Runner_Version' ) ) {
			$version = PHPUnit_Runner_Version::id();
		} elseif ( class_exists( 'PHPUnit\Runner\Version' ) ) {
			// Must be parsable by PHP 5.2.x.
			$version = call_user_func( 'PHPUnit\Runner\Version::id' );
		} else {
			$version = 0;
		}
		return $version;
	}
}

if ( version_compare( tests_get_phpunit_version(), '7.0', '>=' ) ) {
	require dirname( __FILE__ ) . '/phpunit7/speed-trap-listener.php';
} else {
	require dirname( __FILE__ ) . '/speed-trap-listener.php';
}
