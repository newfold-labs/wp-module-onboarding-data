<?php

namespace NewfoldLabs\WP\Module\Survey\Data;

use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * Manages all the constants for the module.
 */
class Constants {
	/**
	 * Constructor for the Constants class.
	 *
	 * @param Container $container The module container.
	 */
	public function __construct( $container ) {
		if ( ! defined( 'NFD_SURVEY_VERSION' ) ) {
			define( 'NFD_SURVEY_VERSION', '1.0.2' );
		}
		if ( ! defined( 'NFD_SURVEY_BUILD_DIR' ) && defined( 'NFD_SURVEY_VERSION' ) ) {
			define( 'NFD_SURVEY_BUILD_DIR', dirname( __DIR__, 2 ) . '/build/' . NFD_SURVEY_VERSION );
		}
		if ( ! defined( 'NFD_SURVEY_BUILD_URL' && defined( 'NFD_SURVEY_VERSION' ) ) ) {
			define( 'NFD_SURVEY_BUILD_URL', $container->plugin()->url . '/vendor/newfold-labs/wp-module-survey/build/' . NFD_SURVEY_VERSION );
		}
	}
}
