<?php
namespace NewfoldLabs\WP\Module\Features;

use WP_Error;
use NewfoldLabs\WP\Module\Features\Features;

/**
 * Helper function to check if a feature is enabled by name
 *
 * @param string $name - the feature name
 * @return bool indicating if the feature is enabled
 */
function isEnabled( $name ) {
	if ( Features::getInstance()->hasFeature( $name ) ) {
		return Features::getInstance()->getFeature( $name )->isEnabled();
	} else {
		return new WP_Error(
			'nfd_features_error',
			'Feature not found.',
			array( 'status' => 404 )
		);
	}
}

/**
 * Helper function to enable a feature by name
 *
 * @param string $name - the feature name
 * @return bool indicating if the feature was enabled
 */
function enable( $name ) {
	if ( Features::getInstance()->hasFeature( $name ) ) {
		return Features::getInstance()->getFeature( $name )->enable();
	} else {
		return new WP_Error(
			'nfd_features_error',
			'Feature not found.',
			array( 'status' => 404 )
		);
	}
}

/**
 * Helper function to disable a feature by name
 *
 * @param string $name - the feature name
 * @return bool indicating if the feature was disabled
 */
function disable( $name ) {
	if ( Features::getInstance()->hasFeature( $name ) ) {
		return Features::getInstance()->getFeature( $name )->disable();
	} else {
		return new WP_Error(
			'nfd_features_error',
			'Feature not found.',
			array( 'status' => 404 )
		);
	}
}
