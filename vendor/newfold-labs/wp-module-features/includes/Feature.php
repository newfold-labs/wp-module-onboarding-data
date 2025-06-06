<?php

namespace NewfoldLabs\WP\Module\Features;

use NewfoldLabs\WP\Module\Features\Registry;
use WP_Forge\Options\Options;

/**
 * Base class for a feature in the Newfold plugin.
 *
 * Child classes should define a name property as the feature name for all API calls. This name will be used in the registry.
 * Child class naming convention is {FeatureName}Feature.
 */
abstract class Feature {

	/**
	 * Options object
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * The feature name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The feature value.
	 *
	 * @var boolean
	 */
	protected $value = false;

	/**
	 * Constructor
	 *
	 * @param Options $options The associated Options for saving to database
	 */
	final public function __construct( $options ) {

		// assign options
		$this->options = $options;
		$this->setValue(
			// check if state already saved to options
			$this->options->get(
				$this->name,
				// use default value via defaultValue filter
				apply_filters(
					"newfold/features/filter/defaultValue:{$this->name}",
					$this->value
				)
			)
		);

		// only initialize if enabled
		if ( $this->isEnabled() ) {
			$this->initialize();

			// specific feature onInitialize action
			do_action( "newfold/features/action/onInitialize:{$this->name}" );
		}

		// else not initialized or loaded - does nothing
	}

	/**
	 * Init
	 *
	 * Add this in the child feature class.
	 */
	protected function initialize() {
		// do initialization stuff - nothing here but in the child class
	}

	/**
	 * Set Value - this updates the value as well as the option
	 *
	 * @param boolean $value The value to set.
	 */
	private function setValue( $value ) {
		$this->value = $value;
		$this->options->set( $this->name, $value );
	}

	/**
	 * Enables the feature.
	 *
	 * @return boolean True if successful, false otherwise
	 */
	final public function enable() {
		if ( $this->isTogglable() ) {

			// generic feature beforeEnable action
			do_action( 'newfold/features/action/beforeEnable', $this->name );
			// specific feature beforeEnable action
			do_action( "newfold/features/action/beforeEnable:{$this->name}" );

			// generic feature onEnable action
			do_action( 'newfold/features/action/onEnable', $this->name );
			// specific feature onEnable action
			do_action( "newfold/features/action/onEnable:{$this->name}" );
			$this->setValue( true );

			// generic feature afterEnable action
			do_action( 'newfold/features/action/afterEnable', $this->name );
			// specific feature afterEnable action
			do_action( "newfold/features/action/afterEnable:{$this->name}" );

			return true;
		}
		return false;
	}

	/**
	 * Disables the feature.
	 *
	 * @return boolean True if successful, false otherwise
	 */
	final public function disable() {
		if ( $this->isTogglable() ) {

			// generic feature beforeDisable action
			do_action( 'newfold/features/action/beforeDisable', $this->name );
			// specific feature beforeDisable action
			do_action( "newfold/features/action/beforeDisable:{$this->name}" );

			// generic feature onDisable action
			do_action( 'newfold/features/action/onDisable', $this->name );
			// specific feature onDisable action
			do_action( "newfold/features/action/onDisable:{$this->name}" );
			$this->setValue( false );

			// generic feature afterDisable action
			do_action( 'newfold/features/action/afterDisable', $this->name );
			// specific feature afterDisable action
			do_action( "newfold/features/action/afterDisable:{$this->name}" );

			return true;
		}
		return false;
	}

	/**
	 * Checks if the feature is enabled.
	 *
	 * @return bool True if the feature is enabled, false otherwise.
	 */
	final public function isEnabled() {
		return apply_filters(
			// specific feature isEnabled filter
			"newfold/features/filter/isEnabled:{$this->name}",
			apply_filters(
				// generic isEnabled filter
				'newfold/features/filter/isEnabled',
				$this->value
			)
		);
	}

	/**
	 * Determine if the features toggle is allowed
	 *
	 * @return bool True if the feature can toggle, false otherwise.
	 */
	final public function isTogglable() {
		return (bool) apply_filters(
			// specific feature canToggle filter
			"newfold/features/filter/canToggle:{$this->name}",
			apply_filters(
				// generic canToggle filter
				'newfold/features/filter/canToggle',
				$this->canToggle()
			)
		);
	}

	/**
	 * Check if the feature can be toggled with current permissions.
	 *
	 * @return bool True if the feature toggle is allowed, false otherwise.
	 */
	public function canToggle() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get Name
	 */
	public function getName() {
		return $this->name;
	}
}
