<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data;

/**
 * Options wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Onboarding\Data\Options
 */
class OptionsWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * get_option_name returns prefixed option name for known key.
	 *
	 * @return void
	 */
	public function test_get_option_name_with_prefix() {
		$name = Options::get_option_name( 'redirect', true );
		$this->assertSame( 'nfd_module_onboarding_should_redirect', $name );
	}

	/**
	 * get_option_name returns unprefixed option name when attach_prefix is false.
	 *
	 * @return void
	 */
	public function test_get_option_name_without_prefix() {
		$name = Options::get_option_name( 'redirect', false );
		$this->assertSame( 'should_redirect', $name );
	}

	/**
	 * get_option_name returns false for unknown key.
	 *
	 * @return void
	 */
	public function test_get_option_name_unknown_key_returns_false() {
		$this->assertFalse( Options::get_option_name( 'nonexistent_key' ) );
	}

	/**
	 * get_all_options returns non-empty array.
	 *
	 * @return void
	 */
	public function test_get_all_options() {
		$options = Options::get_all_options();
		$this->assertIsArray( $options );
		$this->assertArrayHasKey( 'redirect', $options );
		$this->assertArrayHasKey( 'compatibility_results', $options );
	}

	/**
	 * get_initialization_options returns expected keys and values.
	 *
	 * @return void
	 */
	public function test_get_initialization_options() {
		$opts = Options::get_initialization_options();
		$this->assertIsArray( $opts );
		$this->assertArrayHasKey( 'permalink_structure', $opts );
		$this->assertSame( '/%postname%/', $opts['permalink_structure'] );
	}

	/**
	 * get_wc_settings_options returns WooCommerce option definitions.
	 *
	 * @return void
	 */
	public function test_get_wc_settings_options() {
		$wc = Options::get_wc_settings_options();
		$this->assertIsArray( $wc );
		$this->assertArrayHasKey( 'wc_currency', $wc );
		$this->assertArrayHasKey( 'show_in_rest', $wc['wc_currency'] );
		$this->assertTrue( $wc['wc_currency']['show_in_rest'] );
	}
}
