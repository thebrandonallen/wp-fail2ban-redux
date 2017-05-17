<?php
/**
 * The WP_Fail2Ban_Redux class tests.
 *
 * @package WP_Fail2Ban_Redux
 * @subpackage Tests
 */

/**
 * WP_Fail2Ban_Redux tests.
 */
class WP_Fail2Ban_Redux_Tests extends WP_UnitTestCase {

	/**
	 * The setUp method.
	 *
	 * @since 0.3.0
	 */
	public function setUp() {
		parent::setUp();

		$this->spongebob_id = $this->factory->user->create( array(
			'user_login' => 'spongebob',
			'user_email' => 'spongebob@example.com',
		) );

		$this->squidward_id = $this->factory->user->create( array(
			'user_login' => 'squidward',
			'user_email' => 'squidward@example.com',
		) );

		// Get the WP Fail2Ban Redux instance.
		$this->wpf2br = WP_Fail2Ban_Redux::get_instance();

		// Set the logger to our mock.
		$this->wpf2br->set_logger( new WP_Fail2Ban_Redux_Logger_Mock );
	}

	/**
	 * Return an array to block SpongeBob.
	 *
	 * @since 0.3.0
	 *
	 * @param array $blocked The blocked users array.
	 *
	 * @return array
	 */
	public function block_spongebob( $blocked = array() ) {
		return array( 'spongebob' );
	}

	/**
	 * Return an array to block Squidward.
	 *
	 * @since 0.3.0
	 *
	 * @param array $blocked The blocked users array.
	 *
	 * @return array
	 */
	public function block_squidward( $blocked = array() ) {
		return array( 'squidward' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::authenticate` when there are no blocked users.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::authenticate
	 */
	public function test_authenticate_no_blocked_users() {

		$spongebob = get_user_by( 'ID', $this->spongebob_id );

		// No users are blocked.
		$this->expectOutputString( '' );
		$this->assertSame( $spongebob, $this->wpf2br->authenticate( $spongebob, 'spongebob' ) );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::authenticate` when the passed user is not blocked.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::authenticate
	 */
	public function test_authenticate_not_blocked() {

		$spongebob = get_user_by( 'ID', $this->spongebob_id );

		// Squidward is blocked.
		add_filter( 'wp_fail2ban_redux_blocked_users', array( $this, 'block_squidward' ) );

		$this->expectOutputString( '' );
		$this->assertSame( $spongebob, $this->wpf2br->authenticate( $spongebob, 'spongebob' ) );

		remove_filter( 'wp_fail2ban_redux_blocked_users', array( $this, 'block_squidward' ) );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::authenticate` when the passed user is blocked.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::authenticate
	 */
	public function test_authenticate_blocked() {

		$spongebob = get_user_by( 'ID', $this->spongebob_id );

		// SpongeBob is blocked.
		add_filter( 'wp_fail2ban_redux_blocked_users', array( $this, 'block_spongebob' ) );

		$expected = 'openlog:authenticate:syslog:Blocked authentication attempt for spongebob:exit:authenticate';
		$this->expectOutputString( $expected );
		$this->wpf2br->authenticate( $spongebob, 'spongebob' );

		remove_filter( 'wp_fail2ban_redux_blocked_users', array( $this, 'block_spongebob' ) );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::authenticate` when we're blocking users NOT in
	 * the blocked users array.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::authenticate
	 */
	public function test_authenticate_not_in() {

		$squidward = get_user_by( 'ID', $this->squidward_id );

		// SpongeBob is blocked.
		add_filter( 'wp_fail2ban_redux_blocked_users', array( $this, 'block_spongebob' ) );
		add_filter( 'wp_fail2ban_redux_blocked_users_not_in', '__return_true' );

		$expected = 'openlog:authenticate:syslog:Blocked authentication attempt for squidward:exit:authenticate';
		$this->expectOutputString( $expected );
		$this->wpf2br->authenticate( $squidward, 'squidward' );

		remove_filter( 'wp_fail2ban_redux_blocked_users_not_in', '__return_true' );
		remove_filter( 'wp_fail2ban_redux_blocked_users', array( $this, 'block_spongebob' ) );
	}

	/**
	 * Test that `WP_Fail2Ban_Redux::redirect_canonical` returns the passed string.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::redirect_canonical
	 * @expectedDeprecated WP_Fail2Ban_Redux::redirect_canonical
	 */
	public function test_redirect_canonical() {
		$this->assertEquals( 'test', $this->wpf2br->redirect_canonical( 'test' ) );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::xmlrpc_login_error` under normal circumstances.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::xmlrpc_login_error
	 */
	public function test_xmlrpc_login_error() {

		// Test the initial XML-RPC authentication error.
		$expected = 'openlog:xmlrpc_login_error:syslog:XML-RPC authentication failure';
		$this->expectOutputString( $expected );
		$this->assertEquals( 'test', $this->wpf2br->xmlrpc_login_error( 'test' ) );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::xmlrpc_login_error` under for multicall.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::xmlrpc_login_error
	 */
	public function test_xmlrpc_login_error_multicall() {

		$this->wpf2br->xmlrpc_login_error( 'test' );
		ob_clean();

		// Test multicall logging.
		$expected = 'openlog:xmlrpc_login_error:syslog:XML-RPC authentication failure:syslog:XML-RPC multicall authentication failure';
		$this->expectOutputString( $expected );
		$this->wpf2br->xmlrpc_login_error( 'test' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::xmlrpc_pingback_error` for error codes not 48.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::xmlrpc_pingback_error
	 */
	public function test_xmlrpc_pingback_error() {

		$error = new stdClass;
		$error->code = 0;

		$expected = "openlog:xmlrpc_pingback_error:syslog:Pingback error {$error->code} generated";
		$this->expectOutputString( $expected );
		$this->assertSame( $error, $this->wpf2br->xmlrpc_pingback_error( $error ) );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::xmlrpc_pingback_error` for error code 48.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::xmlrpc_pingback_error
	 */
	public function test_xmlrpc_pingback_error_pingback_exists() {

		$error = new stdClass;
		$error->code = 48;

		$this->expectOutputString( '' );
		$this->assertSame( $error, $this->wpf2br->xmlrpc_pingback_error( $error ) );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::comment_spam` when a comment is spam.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::comment_spam
	 */
	public function test_comment_spam() {

		add_filter( 'wp_fail2ban_redux_log_spam_comments', '__return_true' );

		// Fake a comment, and cache it so `get_comment()` can find it.
		$comment = new stdClass;
		$comment->ID = 20030527;
		$comment->status = 'spam';
		wp_cache_add( $comment->ID, $comment, 'comment' );

		$expected = 'openlog:comment_spam:syslog:Spammed comment';
		$this->expectOutputString( $expected );
		$this->wpf2br->comment_spam( $comment->ID, $comment->status );

		wp_cache_delete( $comment->ID, 'comment' );
		remove_filter( 'wp_fail2ban_redux_log_spam_comments', '__return_true' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::comment_spam` when comment filtering is disabled.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::comment_spam
	 */
	public function test_comment_spam_disabled() {

		// Test spam comments not being logged.
		$this->expectOutputString( '' );
		$this->assertSame( null, $this->wpf2br->comment_spam( 1, 1 ) );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::comment_spam` when comment logging is disabled.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::comment_spam
	 */
	public function test_comment_spam_not_spam() {

		add_filter( 'wp_fail2ban_redux_log_spam_comments', '__return_true' );

		$this->expectOutputString( '' );
		$this->assertSame( null, $this->wpf2br->comment_spam( 20030527, 1 ) );

		remove_filter( 'wp_fail2ban_redux_log_spam_comments', '__return_true' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::comment_spam` when a comment doesn't exist.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::comment_spam
	 */
	public function test_comment_spam_not_spam_comment_not_exists() {

		add_filter( 'wp_fail2ban_redux_log_spam_comments', '__return_true' );

		$this->expectOutputString( '' );
		$this->assertSame( null, $this->wpf2br->comment_spam( 20030527, 'spam' ) );

		remove_filter( 'wp_fail2ban_redux_log_spam_comments', '__return_true' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::user_enumeration` for successful block.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::user_enumeration
	 */
	public function test_user_enumeration() {

		$_GET['author'] = '1';

		$this->set_permalink_structure( '/posts/%postname%/' );

		add_filter( 'wp_fail2ban_redux_block_user_enumeration', '__return_true' );

		$expected = 'openlog:user_enumeration:syslog:Blocked user enumeration attempt:exit:user_enumeration';
		$this->expectOutputString( $expected );
		$this->wpf2br->user_enumeration();

		remove_filter( 'wp_fail2ban_redux_block_user_enumeration', '__return_true' );
		$this->set_permalink_structure();
	}

	/**
	 * Test `WP_Fail2Ban_Redux::user_enumeration` when blocking disabled.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::user_enumeration
	 */
	public function test_user_enumeration_not_blocking() {
		$this->expectOutputString( '' );
		$this->wpf2br->user_enumeration();
	}

	/**
	 * Test `WP_Fail2Ban_Redux::user_enumeration` with no author query vars.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::user_enumeration
	 */
	public function test_user_enumeration_no_query_vars() {

		add_filter( 'wp_fail2ban_redux_block_user_enumeration', '__return_true' );

		$this->expectOutputString( '' );
		$this->wpf2br->user_enumeration();

		remove_filter( 'wp_fail2ban_redux_block_user_enumeration', '__return_true' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::user_enumeration` when in the admin.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::user_enumeration
	 */
	public function test_user_enumeration_in_admin() {

		$_GET['author'] = '1';

		$hook                   = parse_url( 'index.php' );
		$GLOBALS['hook_suffix'] = $hook['path'];
		set_current_screen();

		add_filter( 'wp_fail2ban_redux_block_user_enumeration', '__return_true' );

		$this->expectOutputString( '' );
		$this->wpf2br->user_enumeration();

		set_current_screen( 'front' );
		unset( $GLOBALS['screen'] );
		remove_filter( 'wp_fail2ban_redux_block_user_enumeration', '__return_true' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::user_enumeration` with pretty permalinks disabled.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::user_enumeration
	 */
	public function test_user_enumeration_no_pretty_permalinks() {

		$_GET['author'] = '1';

		$this->set_permalink_structure();

		add_filter( 'wp_fail2ban_redux_block_user_enumeration', '__return_true' );

		$this->expectOutputString( '' );
		$this->wpf2br->user_enumeration();

		remove_filter( 'wp_fail2ban_redux_block_user_enumeration', '__return_true' );
		$this->set_permalink_structure();
	}

	/**
	 * Test `WP_Fail2Ban_Redux::wp_login`.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::wp_login
	 */
	public function test_wp_login() {

		$expected = 'openlog:wp_login:syslog:Accepted password for spongebob';
		$this->expectOutputString( $expected );
		$this->wpf2br->wp_login( 'spongebob' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::wp_login_failed` for unknown user.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::wp_login_failed
	 */
	public function test_wp_login_failed_unknown_user() {

		$expected = 'openlog:wp_login_failed:syslog:Authentication attempt for unknown user patrick';
		$this->expectOutputString( $expected );
		$this->wpf2br->wp_login_failed( 'patrick' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::wp_login_failed` when a user_login is passed as
	 * the username.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::wp_login_failed
	 */
	public function test_wp_login_failed_user_login() {

		// Make sure SpongeBob is cached.
		get_user_by( 'ID', $this->spongebob_id );

		$expected = 'openlog:wp_login_failed:syslog:Authentication failure for spongebob';
		$this->expectOutputString( $expected );
		$this->wpf2br->wp_login_failed( 'spongebob' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::wp_login_failed` when a user_email is passed as
	 * the username.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::wp_login_failed
	 */
	public function test_wp_login_failed_email() {

		// Make sure SpongeBob is cached.
		get_user_by( 'ID', $this->spongebob_id );

		$expected = 'openlog:wp_login_failed:syslog:Authentication failure for spongebob';
		$this->expectOutputString( $expected );
		$this->wpf2br->wp_login_failed( 'spongebob@example.com' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::xmlrpc_call` for a legitimate pingback.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::xmlrpc_call
	 */
	public function test_xmlrpc_call() {

		// Create a fake `wp_xmlrpc_server` object.
		$wp_xmlrpc_server                  = new stdClass;
		$wp_xmlrpc_server->message         = new stdClass;
		$wp_xmlrpc_server->message->params = array(
			1 => 'http://spongebob.example.com',
		);
		$GLOBALS['wp_xmlrpc_server'] = $wp_xmlrpc_server;

		add_filter( 'wp_fail2ban_redux_log_pingbacks', '__return_true' );

		$expected = "openlog:xmlrpc_call_pingback:syslog:Pingback requested for 'http://spongebob.example.com'";
		$this->expectOutputString( $expected );
		$this->wpf2br->xmlrpc_call( 'pingback.ping' );

		unset( $GLOBALS['wp_xmlrpc_server'] );
		remove_filter( 'wp_fail2ban_redux_log_pingbacks', '__return_true' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::xmlrpc_call` when no url parameter is passed.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::xmlrpc_call
	 */
	public function test_xmlrpc_call_no_url_param() {

		global $wp_xmlrpc_server;

		// Create a fake `wp_xmlrpc_server` object.
		$wp_xmlrpc_server = new stdClass;

		add_filter( 'wp_fail2ban_redux_log_pingbacks', '__return_true' );

		$expected = "openlog:xmlrpc_call_pingback:syslog:Pingback requested for 'unknown'";
		$this->expectOutputString( $expected );
		$this->wpf2br->xmlrpc_call( 'pingback.ping' );

		unset( $GLOBALS['wp_xmlrpc_server'] );
		remove_filter( 'wp_fail2ban_redux_log_pingbacks', '__return_true' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::xmlrpc_call` that's not a pingback.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::xmlrpc_call
	 */
	public function test_xmlrpc_call_not_pingback() {

		$this->expectOutputString( '' );
		$this->wpf2br->xmlrpc_call( 'test' );
	}

	/**
	 * Test `WP_Fail2Ban_Redux::xmlrpc_call` when not logging pingbacks.
	 *
	 * @since 0.3.0
	 *
	 * @covers WP_Fail2Ban_Redux::xmlrpc_call
	 */
	public function test_xmlrpc_call_not_logging_pingbacks() {

		$this->expectOutputString( '' );
		$this->wpf2br->xmlrpc_call( 'pingback.ping' );
	}
}
