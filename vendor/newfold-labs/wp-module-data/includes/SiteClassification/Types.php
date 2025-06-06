<?php

namespace NewfoldLabs\WP\Module\Data\SiteClassification;

/**
 * Base class with common functionality across different site classification types.
 */
abstract class Types {
	/**
	 * The option name to store the data.
	 *
	 * @var string
	 */
	public $option_name;

	/**
	 * Indicates what the value refers to, slug(from default slugs)|custom(from a custom input field).
	 *
	 * @var string
	 */
	public $refers;

	/**
	 * The actual value of the site classification type.
	 *
	 * @var string
	 */
	public $value;

	/**
	 * Constructor for the Type class.
	 *
	 * @param string $option_name The option name to store the data.
	 * @param string $refers Indicates what the value refers to, slug(from default slugs)|custom(from a custom input field).
	 * @param string $value The actual value of the site classification type.
	 */
	public function __construct( $option_name, $refers, $value ) {
		$this->option_name = $option_name;
		$this->refers      = $refers;
		$this->value       = $value;
	}

	/**
	 * Sets the refers property.
	 *
	 * @param string $refers Indicates what the value refers to, slug(from default slugs)|custom(from a custom input field).
	 * @return void
	 */
	public function set_refers( $refers ) {
		$this->refers = $refers;
	}

	/**
	 * Sets the value property.
	 *
	 * @param string $value The actual value of the site classification type.
	 * @return void
	 */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 * Saves data to the option after validation.
	 *
	 * @return boolean
	 */
	public function save() {
		if ( ! $this->validate() ) {
			return false;
		}
		update_option( $this->option_name, $this->to_array() );
		return true;
	}

	/**
	 * Converts the object to an array.
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'refers' => $this->refers,
			'value'  => $this->value,
		);
	}

	/**
	 * Validates the site classification type data.
	 *
	 * @return boolean
	 */
	abstract public function validate();
}
