<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data;

use NewfoldLabs\WP\Module\Onboarding\Data\Flows\Flows;

/**
 * Module loading wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Onboarding\Data\Options
 */
class ModuleLoadingWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Verify core module classes exist.
	 *
	 * @return void
	 */
	public function test_module_classes_load() {
		$this->assertTrue( class_exists( Options::class ) );
		$this->assertTrue( class_exists( Config::class ) );
		$this->assertTrue( class_exists( Brands::class ) );
		$this->assertTrue( class_exists( Data::class ) );
		$this->assertTrue( class_exists( Flows::class ) );
		$this->assertTrue( class_exists( Languages::class ) );
	}

	/**
	 * Verify WordPress factory is available.
	 *
	 * @return void
	 */
	public function test_wordpress_factory_available() {
		$this->assertTrue( function_exists( 'get_option' ) );
		$this->assertNotEmpty( get_option( 'blogname' ) );
	}
}
