<?php

namespace NewfoldLabs\WP\ModuleLoader;

use InvalidArgumentException;
use WP_Forge\Fluent\Fluent;

/**
 * Module class.
 *
 * @property callable $callback
 * @property boolean  $isActive
 * @property boolean  $isHidden
 * @property string   $label
 * @property string   $name
 *
 * @method void callback( callable $callback )
 * @method void isActive( boolean $isActive )
 * @method void isHidden( boolean $isHidden )
 * @method void label( string $label )
 * @method void name( string $name )
 */
class Module extends Fluent {

	/**
	 * Create a new module instance.
	 *
	 * @param array $attributes Module attributes
	 *
	 * @return Module
	 */
	public static function make( $attributes = [] ) {
		return new self( $attributes );
	}

	/**
	 * Constructor.
	 *
	 * @param array $attributes Module attributes
	 */
	public function __construct( $attributes = [] ) {
		$defaults = [
			'isActive' => false,
			'isHidden' => false,
		];
		parent::__construct( array_merge( $defaults, $attributes ) );
	}

	/**
	 * Validate module.
	 *
	 * @return bool
	 *
	 * @throws InvalidArgumentException
	 */
	public function validate() {

		if ( ! $this->has( 'name' ) ) {
			throw new InvalidArgumentException( 'Module must have a name!' );
		}

		if ( ! is_string( $this->name ) ) {
			throw new InvalidArgumentException( 'Module `name` argument must be a string!' );
		}

		if ( ! $this->has( 'label' ) ) {
			throw new InvalidArgumentException( 'Module must have a label!' );
		}

		if ( ! is_string( $this->label ) ) {
			throw new InvalidArgumentException( 'Module `label` argument must be a string!' );
		}

		if ( ! $this->has( 'callback' ) ) {
			throw new InvalidArgumentException( 'Module must have a callback!' );
		}

		if ( ! is_callable( $this->callback ) ) {
			throw new InvalidArgumentException( 'Module must have a valid callback!' );
		}

		if ( ! is_bool( $this->get( 'isActive' ) ) ) {
			throw new InvalidArgumentException( 'Module `isActive` argument must be a boolean!' );
		}

		if ( ! is_bool( $this->get( 'isHidden' ) ) ) {
			throw new InvalidArgumentException( 'Module `isHidden` argument must be a boolean!' );
		}

		return true;

	}

	/**
	 * Register the module.
	 */
	public function register() {
		$this->validate();
		ModuleRegistry::register( $this );
	}

}
