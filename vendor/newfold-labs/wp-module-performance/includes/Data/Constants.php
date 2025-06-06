<?php

namespace NewfoldLabs\WP\Module\Performance\Data;

use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * Manages all the constants for the performance module.
 */
class Constants {
	/**
	 * Constructor for the Constants class.
	 *
	 * @param Container $container The module container.
	 */
	public function __construct( $container ) {
		if ( ! defined( 'NFD_PERFORMANCE_BUILD_DIR' ) ) {
			define( 'NFD_PERFORMANCE_BUILD_DIR', dirname( __DIR__, 2 ) . '/build' );
		}

		if ( ! defined( 'NFD_PERFORMANCE_BUILD_URL' ) ) {
			define( 'NFD_PERFORMANCE_BUILD_URL', $container->plugin()->url . 'vendor/newfold-labs/wp-module-performance/build' );
		}

		if ( ! defined( 'NFD_MODULE_DATA_EVENTS_API' ) ) {
			define( 'NFD_MODULE_DATA_EVENTS_API', '/newfold-data/v1/events' );
		}
	}
}
