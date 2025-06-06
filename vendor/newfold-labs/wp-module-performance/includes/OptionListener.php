<?php

namespace NewfoldLabs\WP\Module\Performance;

/**
 * Class to monitor changes to an option.
 */
class OptionListener {

	/**
	 * Callback function to be called on change.
	 *
	 * @var callable
	 */
	protected $callable;

	/**
	 * Name of the option to monitor.
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * Constructor
	 *
	 * @param string   $option_name The name of the option to monitor.
	 * @param callable $callback    The callback function to be called on change.
	 */
	public function __construct( string $option_name, callable $callback ) {

		$this->callable = $callback;
		$this->option   = $option_name;

		add_action( "add_option_{$option_name}", array( $this, 'onAdd' ), 10, 2 );
		add_action( "update_option_{$option_name}", array( $this, 'onUpdate' ), 10, 2 );
		add_action( "delete_option_{$option_name}", array( $this, 'onDelete' ) );
	}

	/**
	 * Call function when a new option value is added.
	 *
	 * @param string $option The option name.
	 * @param mixed  $value  The option value.
	 */
	public function onAdd( $option, $value ) {
		call_user_func( $this->callable, $value, $option );
	}

	/**
	 * Call function when an option value is updated.
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $new_value The new option value.
	 */
	public function onUpdate( $old_value, $new_value ) {
		if ( $old_value !== $new_value ) {
			call_user_func( $this->callable, $new_value, $this->option );
		}
	}

	/**
	 * Call function when an option is deleted.
	 */
	public function onDelete() {
		call_user_func( $this->callable, null, $this->option );
	}
}
