<?php

namespace NewfoldLabs\WP\ModuleLoader;

use WP_Forge\Options\Options;

/**
 * Register a module.
 *
 * Required attributes:
 *  - name (string): The internal module name; should be lowercase with dashes.
 *  - label (string): The user-facing module name
 *  - callback (callable): The callback that kicks off the module's functionality.
 *  - isActive (bool): Whether the module defaults to active.
 *  - isHidden (bool): Whether the module should be hidden from users in the UI.
 *
 * @param iterable $attributes
 */
function register( $attributes ) {
	$module = new Module( $attributes );
	$module->register();
}

/**
 * Unregister a module.
 *
 * @param string $name Module name
 */
function unregister( string $name ) {
	ModuleRegistry::unregister( $name );
}

/**
 * Activate a module.
 *
 * @param string $name Module name
 */
function activate( string $name ) {

	if ( ModuleRegistry::has( $name ) ) {

		/** @var Module $module */
		$module = ModuleRegistry::get( $name );

		// Activate module
		$module->isActive = true;
		options()->set( $name, true );
	}
}

/**
 * Deactivate a module.
 *
 * @param string $name Module name
 */
function deactivate( string $name ) {

	if ( ModuleRegistry::has( $name ) ) {

		/** @var Module $module */
		$module = ModuleRegistry::get( $name );

		// Deactivate module
		$module->isActive = false;
		options()->set( $name, false );

	}
}

/**
 * Check if a module is active.
 *
 * @param string $name Module name
 *
 * @return bool
 */
function isActive( string $name ) {
	return ModuleRegistry::getActive()->has( $name );
}

/**
 * Load all active modules.
 *
 * @return void
 */
function load() {
	// Load active modules
	foreach ( ModuleRegistry::getActive() as $module ) {
		call_user_func( $module->callback, container(), $module );
	}

	// Store state of all modules into the database
	options()->populate( ModuleRegistry::collection()->pluck( 'isActive', 'name' )->all() );
}

/**
 * Get or set the container instance.
 *
 * @param Container|null $container
 *
 * @return Container
 */
function container( Container $container = null ) {

	static $instance;

	// If no container was passed and was never set, default to an empty container
	if ( ! isset( $instance ) ) {
		// If a container was passed, set it; otherwise default to an empty container.
		$instance = ! is_null( $container ) ? $container : new Container();

		do_action( 'newfold_container_set', $instance );
	}

	return $instance;
}

/**
 * Get the options object for our active modules.
 *
 * @return Options
 */
function options() {

	static $options;

	if ( ! isset( $options ) ) {
		$options = new Options( 'newfold_active_modules' );
	}

	return $options;
}
