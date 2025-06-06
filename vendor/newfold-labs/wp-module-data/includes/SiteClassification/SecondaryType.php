<?php

namespace NewfoldLabs\WP\Module\Data\SiteClassification;

/**
 * Class SecondaryType
 *
 * Class that manages the secondary site classification type.
 *
 * @package NewfoldLabs\WP\Module\Data
 */
final class SecondaryType extends Types {
	/**
	 * Name of the site classification secondary option.
	 *
	 * @var string
	 */
	public static $secondary_option_name = 'nfd_data_site_classification_secondary';

	/**
	 * Constructor for the SecondaryType class.
	 *
	 * @param string $refers Indicates what the value refers to, slug(from default slugs)|custom(from a custom input field).
	 * @param string $value The actual value of the site classification type.
	 */
	public function __construct( $refers, $value ) {
		parent::__construct( self::$secondary_option_name, $refers, $value );
	}

	/**
	 * Validates the data.
	 *
	 * @return boolean
	 */
	public function validate() {
		// If the secondary type refers to a custom value (from a user input field) we cannot validate the value.
		if ( 'custom' === $this->refers ) {
			return true;
		}

		// Retrieve the selected primary type.
		$primary = PrimaryType::instantiate_from_option();
		// If it does not exist, then give benefit of doubt.
		if ( ! ( $primary instanceof PrimaryType ) ) {
			return true;
		}

		$classification  = SiteClassification::get();
		$secondary_types = $classification['types'][ $primary->value ]['secondaryTypes'];
		// If secondaryTypes does not exist or the selected slug does not exist then return false.
		if ( ! isset( $secondary_types ) || ! isset( $secondary_types[ $this->value ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Instantiates a class object from the data stored in the option.
	 *
	 * @return SecondaryType|boolean
	 */
	public static function instantiate_from_option() {
		$data = get_option( self::$secondary_option_name, false );

		if ( ! $data || ! is_array( $data ) || ! isset( $data['refers'] ) || ! isset( $data['value'] ) ) {
			delete_option( self::$secondary_option_name );
			return false;
		}

		$instance = new self( $data['refers'], $data['value'] );
		if ( ! $instance->validate() ) {
			delete_option( self::$secondary_option_name );
			return false;
		}

		return $instance;
	}
}
