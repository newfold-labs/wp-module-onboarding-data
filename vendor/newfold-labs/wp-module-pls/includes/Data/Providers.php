<?php

namespace NewfoldLabs\WP\Module\PLS\Data;

/**
 * Class Providers
 *
 * Handles provider-specific actions for the PLS module.
 */
class Providers {

	/**
	 * Environment for constructing option names.
	 *
	 * @var string
	 */
	private $environment;

	/**
	 * Providers array for option name rules and storage method.
	 *
	 * @var array
	 */
	private $providers;

	/**
	 * Constructor for Providers.
	 */
	public function __construct() {
		// Define the environment using NFD_PLS_ENV.
		if ( ! defined( 'NFD_PLS_ENV' ) ) {
			define( 'NFD_PLS_ENV', 'production' );
		}
		$this->environment = constant( 'NFD_PLS_ENV' );

		// Initialize provider-specific option rules and storage method.
		$this->providers = array(
			'yith' => array(
				'storage' => array(
					'license_id'     => "pls_license_id_{$this->environment}_%s",
					'activation_key' => "pls_activation_key_{$this->environment}_%s",
					'method'         => 'wp_option',
				),
			),
			// This fallback provider is used when we do not receive the storage map for any provider from Hiive after provisioning a license
			'nfd'  => array(
				'storage' => array(
					'license_id'     => "nfd_module_pls_license_id_{$this->environment}_%s",
					'activation_key' => "nfd_module_pls_activation_key_{$this->environment}_%s",
				),
			),
		);
	}

	/**
	 * Get the option name based on the provider, storage type, and identifier (license_id or activation_key).
	 *
	 * @param string $provider The provider name (e.g., "yith").
	 * @param string $type The type of option name to retrieve ('license_id' or 'activation_key').
	 * @param string $identifier The identifier to insert into the option name (plugin_slug or license_id).
	 *
	 * @return string|false The constructed option name or false if provider/type is not found.
	 */
	public function get_option_name( $provider, $type, $identifier ) {
		if ( isset( $this->providers[ $provider ]['storage'][ $type ] ) ) {
			return sprintf( $this->providers[ $provider ]['storage'][ $type ], $identifier );
		}

		return sprintf( $this->providers['nfd']['storage'][ $type ], $identifier );
	}

	/**
	 * Get the option name for storing the license ID for a given provider.
	 *
	 * @param string $provider The provider name (e.g., "yith").
	 * @param string $plugin_slug The plugin slug.
	 *
	 * @return string|false The option name for storing the license ID.
	 */
	public function get_license_id_option_name( $provider, $plugin_slug ) {
		return $this->get_option_name( $provider, 'license_id', $plugin_slug );
	}

	/**
	 * Get the option name for storing the activation key for a given provider.
	 *
	 * @param string $provider The provider name (e.g., "yith").
	 * @param string $license_id The license ID.
	 *
	 * @return string|false The option name for storing the activation key.
	 */
	public function get_activation_key_option_name( $provider, $license_id ) {
		return $this->get_option_name( $provider, 'activation_key', $license_id );
	}

	/**
	 * Get the storage method for a given provider.
	 *
	 * @param string $provider The provider name (e.g., "yith").
	 *
	 * @return string|false The storage method for the provider.
	 */
	public function get_storage_method( $provider ) {
		return $this->providers[ $provider ]['storage']['method'] ?? false;
	}
}
