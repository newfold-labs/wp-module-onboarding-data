<?php

namespace NewfoldLabs\WP\Module\Performance;

use function NewfoldLabs\WP\ModuleLoader\container as getContainer;

/**
 * Set up the performance feature.
 */
class PerformanceFeature extends \NewfoldLabs\WP\Module\Features\Feature {

	/**
	 * The feature name.
	 *
	 * @var string
	 */
	protected $name = 'performance';

	/**
	 * The feature value. Defaults to on.
	 *
	 * @var boolean
	 */
	protected $value = true; // default to on

	/**
	 * Initialize performance feature.
	 */
	public function initialize() {
		if ( function_exists( 'add_action' ) ) {
			// Register module
			add_action(
				'plugins_loaded',
				function () {
					new Performance( getContainer() );
				}
			);
		}
	}
}
