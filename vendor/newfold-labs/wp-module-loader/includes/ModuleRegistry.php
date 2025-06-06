<?php

namespace NewfoldLabs\WP\ModuleLoader;

use WP_Forge\Collection\Collection;

class ModuleRegistry {

	/**
	 * Register a module.
	 *
	 * @param Module $module Module object
	 */
	public static function register( Module $module ) {

		// Set isActive from the value in the database, otherwise, fall back to the default isActive state
		$module->isActive = options()->get( $module->name, $module->isActive );

		// Add module to the collection
		self::collection()->put( $module->name, $module );
	}

	/**
	 * Unregister a module.
	 *
	 * @param string $name Module name
	 */
	public static function unregister( string $name ) {

		// Set module as inactive in the database.
		options()->set( $name, false );

		// Remove the module from the collection
		self::collection()->forget( $name );
	}

	/**
	 * Get a module by name.
	 *
	 * @param string $name Module name
	 *
	 * @return Module|null
	 */
	public static function get( string $name ) {
		return self::collection()->get( $name );
	}

	/**
	 * Check if a module has been registered.
	 *
	 * @param string $name Module name
	 *
	 * @return bool
	 */
	public static function has( string $name ) {
		return self::collection()->has( $name );
	}

	/**
	 * Get a collection containing active modules.
	 *
	 * @return Collection
	 */
	public static function getActive() {
		return self::collection()->where( 'isActive', '===', true );
	}

	/**
	 * Get the collection of registered modules.
	 *
	 * @return Collection
	 */
	public static function collection() {
		static $collection;
		if ( ! isset( $collection ) ) {
			$collection = Collection::make();
		}

		return $collection;
	}

}
