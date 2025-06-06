<?php
/**
 * Context Boostrap
 *
 * @package NewfoldLabs\WP\Context
 */

use NewfoldLabs\WP\Context\Context;
use function NewfoldLabs\WP\Context\setContext;

if ( function_exists( 'add_action' ) ) {

	// Add context to container on plugins_loaded
	add_action(
		'plugins_loaded',
		function () {
			// Run any registered hooks to set context
			do_action( 'newfold/context/set' );
		},
		// Using higher priority than default to ensure context is set before any module registration
		1
	);

	// Platform detection
	add_action(
		'newfold/context/set',
		function () {
			// set platform
			$platform = 'default';
			if ( defined( 'IS_ATOMIC' ) && IS_ATOMIC ) {
				$platform = 'atomic';
			}
			setContext( 'platform', $platform );
		}
	);
}

if ( function_exists( 'add_filter' ) ) {

	// Add context to runtime
	add_filter(
		'newfold_runtime',
		function ( $runtime ) {
			return array_merge(
				$runtime,
				array( 'context' => Context::all() )
			);
		}
	);

}
