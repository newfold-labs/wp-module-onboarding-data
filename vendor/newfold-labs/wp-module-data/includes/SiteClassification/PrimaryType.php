<?php

namespace NewfoldLabs\WP\Module\Data\SiteClassification;

/**
 * Class PrimaryType
 *
 * Class that manages the primary site classification type.
 *
 * @package NewfoldLabs\WP\Module\Data
 */
final class PrimaryType extends Types {
	/**
	 * Name of the site classification primary option.
	 *
	 * @var string
	 */
	public static $primary_option_name = 'nfd_data_site_classification_primary';

	/**
	 * Constructor for PrimaryType.
	 *
	 * @param string $refers Indicates what the value refers to, slug(from default slugs)|custom(from a custom input field).
	 * @param string $value The actual value of the site classification type.
	 */
	public function __construct( $refers, $value ) {
		parent::__construct( self::$primary_option_name, $refers, $value );
	}

	/**
	 * Validates the data.
	 *
	 * @return boolean
	 */
	public function validate() {
		// If the primary type refers to a custom value (from a user input field) we cannot validate the value.
		if ( 'custom' === $this->refers ) {
			return true;
		}

		// Retrieve the data to validate.
		$classification = SiteClassification::get();
		// Checks if the value is a valid primary type slug.
		if ( ! isset( $classification['types'] ) || ! isset( $classification['types'][ $this->value ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Instantiates a class object from the data stored in the option.
	 *
	 * @return PrimaryType|boolean
	 */
	public static function instantiate_from_option() {
		$data = get_option( self::$primary_option_name, false );

		if ( ! $data || ! is_array( $data ) || ! isset( $data['refers'] ) || ! isset( $data['value'] ) ) {
			delete_option( self::$primary_option_name );
			return false;
		}

		$instance = new self( $data['refers'], $data['value'] );
		if ( ! $instance->validate() ) {
			delete_option( self::$primary_option_name );
			return false;
		}

		return $instance;
	}
}
