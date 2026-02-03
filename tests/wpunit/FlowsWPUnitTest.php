<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data;

use NewfoldLabs\WP\Module\Onboarding\Data\Flows\Flows;

/**
 * Flows wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Onboarding\Data\Flows\Flows
 */
class FlowsWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * get_data returns flow blueprint with expected structure.
	 *
	 * @return void
	 */
	public function test_get_data_returns_flow_structure() {
		$data = Flows::get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'version', $data );
		$this->assertArrayHasKey( 'data', $data );
		$this->assertArrayHasKey( 'currentStep', $data );
		$this->assertArrayHasKey( 'activeFlow', $data );
		$this->assertIsArray( $data['data'] );
		$this->assertArrayHasKey( 'blogName', $data['data'] );
		$this->assertArrayHasKey( 'siteType', $data['data'] );
	}

	/**
	 * get_exception_list returns expected keys.
	 *
	 * @return void
	 */
	public function test_get_exception_list() {
		$list = Flows::get_exception_list();
		$this->assertIsArray( $list );
		$this->assertArrayHasKey( 'other', $list );
		$this->assertTrue( $list['other'] );
	}
}
