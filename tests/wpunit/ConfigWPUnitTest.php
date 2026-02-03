<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data;

/**
 * Config wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Onboarding\Data\Config
 */
class ConfigWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * get_wp_config_initialization_constants returns expected keys.
	 *
	 * @return void
	 */
	public function test_get_wp_config_initialization_constants() {
		$constants = Config::get_wp_config_initialization_constants();
		$this->assertIsArray( $constants );
		$this->assertArrayHasKey( 'AUTOSAVE_INTERVAL', $constants );
		$this->assertArrayHasKey( 'WP_POST_REVISIONS', $constants );
		$this->assertSame( '300', $constants['AUTOSAVE_INTERVAL'] );
		$this->assertSame( '20', $constants['WP_POST_REVISIONS'] );
	}

	/**
	 * is_onboarding_request returns true for same-host URL with page=nfd-onboarding.
	 *
	 * @return void
	 */
	public function test_is_onboarding_request_with_valid_url() {
		$home = home_url( '/' );
		$url = $home . 'wp-admin/admin.php?page=nfd-onboarding';
		$this->assertTrue( Config::is_onboarding_request( $url ) );
	}

	/**
	 * is_onboarding_request returns false when query does not contain page=nfd-onboarding.
	 *
	 * @return void
	 */
	public function test_is_onboarding_request_with_invalid_query() {
		$home = home_url( '/' );
		$url = $home . 'wp-admin/admin.php?page=other';
		$this->assertFalse( Config::is_onboarding_request( $url ) );
	}
}
