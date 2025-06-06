<?php
namespace NewfoldLabs\WP\Module\Features;

use WP_Forge\Options\Options;

/**
 * Registry of Feature instances
 * For managing features within brand plugins.
 */
class Registry {

	/**
	 * Array of Features Instances
	 *
	 * @var Array ( $name => Instance )
	 */
	private $features = array();

	/**
	 * Options object
	 * See https://github.com/wp-forge/wp-options
	 * Key value pairs for feature name and enabled boolean
	 * This is saved to options table in database
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = new Options( 'newfold_features' );
	}

	/**
	 * Checks if a feature is registered.
	 *
	 * @param string $name The feature name.
	 * @return bool True if registered, false otherwise.
	 */
	public function has( $name ) {
		return isset( $this->features[ $name ] );
	}

	/**
	 * Registers a feature with the registry.
	 *
	 * @param string $className The feature class name.
	 */
	public function set( $className ) {
		$instance                = new $className( $this->options );
		$name                    = $instance->getName();
		$this->features[ $name ] = $instance;
	}

	/**
	 * Retrieves a feature instance by name.
	 *
	 * @param string $name The feature name.
	 * @return mixed|null The feature instance if found, null otherwise.
	 */
	public function get( $name ) {
		if ( $this->has( $name ) ) {
			return $this->features[ $name ];
		} else {
			return null;
		}
	}

	/**
	 * Removes a feature from the registry.
	 *
	 * @param string $name The feature name.
	 */
	public function remove( $name ) {
		unset( $this->features[ $name ] );
		$this->options->delete( $name );
	}

	/**
	 * Returns all registered feature names.
	 *
	 * @return array The list of feature names.
	 */
	public function keys() {
		return array_keys( $this->features );
	}

	/**
	 * Retrieves all registered features and values from option
	 *
	 * @return array The list of features.
	 */
	public function all() {
		return $this->options->all();
	}

	/**
	 * Resets the registry by clearing all features.
	 */
	public function reset() {
		// populate with an empty array
		$this->features = array();
		$this->options->populate( array() );
	}
}
