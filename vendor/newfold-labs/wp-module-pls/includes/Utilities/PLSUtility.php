<?php

namespace NewfoldLabs\WP\Module\PLS\Utilities;

use NewfoldLabs\WP\Module\Data\Helpers\Encryption;
use NewfoldLabs\WP\Module\PLS\Data\Providers;
/**
 * Class PLSUtility
 *
 * Provides utility functions for handling license operations.
 */
class PLSUtility {

	/**
	 * The base URL for the PLS API.
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * Option name for storing license storage map as an associative array in WordPress options.
	 *
	 * @var string
	 */
	private $license_storage_map_option_name = 'nfd_module_pls_license_storage_map';

	/**
	 * Constructor for PLSUtility.
	 * Initializes the base URL for the API and sets up other configurations.
	 */
	public function __construct() {
		// Define the base URL for the API if not already defined.
		if ( ! defined( 'NFD_PLS_URL' ) ) {
			define( 'NFD_PLS_URL', 'https://licensing.hiive.cloud' );
		}
		$this->base_url = constant( 'NFD_PLS_URL' );
	}

	/**
	 * Stores the license storage map with encryption.
	 *
	 * @param array<string, array{downloadUrl:string,basename:string,provider:string,activationKeyStorageName:string,licenseIdStorageName:string,storageMethod:string}> $storage_map The license storage map to be encrypted and stored, keyed by plugin slug.
	 */
	public function store_license_storage_map( array $storage_map ) {
		// Initialize the encryption class and encrypt the storage map before storing it.
		$encryption     = new Encryption();
		$encrypted_data = $encryption->encrypt( wp_json_encode( $storage_map ) );
		// Store the encrypted data in the WordPress options table.
		update_option( $this->license_storage_map_option_name, $encrypted_data );
	}

	/**
	 * Retrieves the license storage map with decryption.
	 *
	 * @return array<string, array{licenseIdStorageName?:string,activationKeyStorageName?:string}> The decrypted license storage map, or an empty array on failure.
	 */
	public function retrieve_license_storage_map() {
		// Initialize the encryption class and retrieve the encrypted storage map from WordPress options.
		$encryption     = new Encryption();
		$encrypted_data = get_option( $this->license_storage_map_option_name );
		// Return an empty array if no encrypted data is found.
		if ( ! $encrypted_data ) {
			return array();
		}
		// Decrypt the stored data and return it as an associative array.
		$decrypted_data = $encryption->decrypt( $encrypted_data );
		if ( ! $decrypted_data ) {
			return array();
		}
		return json_decode( $decrypted_data, true );
	}

	/**
	 * Provisions a new license via the Hiive Licensing API using the plugin slug.
	 * If the license is already stored, it returns the stored data.
	 *
	 * @param string $plugin_slug The plugin slug for which the license is being provisioned.
	 * @param string $provider The provider name.
	 *
	 * @return array{licenseId:string,downloadUrl:string,activationKeyStorageName:string,licenseIdStorageName:string,storageMethod:string}|WP_Error License data or WP_Error on failure.
	 */
	public function provision_license( $plugin_slug, $provider ) {
		// Retrieve the existing license storage map.
		$storage_map = $this->retrieve_license_storage_map();

		// Check if a license already exists for the plugin slug.
		if ( isset( $storage_map[ $plugin_slug ] ) ) {
			// If available, get the license ID storage name from the storage map.
			$license_id_storage_name = isset( $storage_map[ $plugin_slug ]['licenseIdStorageName'] )
			? $storage_map[ $plugin_slug ]['licenseIdStorageName'] : null;

			// If the license ID storage name is missing, retrieve it from Providers using the provider name.
			if ( ! $license_id_storage_name ) {
				$provider_instance       = new Providers();
				$license_id_storage_name = $provider_instance->get_license_id_option_name( $provider, $plugin_slug );
			}

			// Check if the license ID is stored in WordPress options.
			$license_id = get_option( $license_id_storage_name );
			if ( $license_id ) {
				// Retrieve the activation key storage name if available in the storage map.
				$activation_key_storage_name = isset( $storage_map[ $plugin_slug ]['activationKeyStorageName'] )
				? $storage_map[ $plugin_slug ]['activationKeyStorageName'] : null;

				// If the activation key storage name is missing, retrieve it from Providers.
				if ( ! $activation_key_storage_name ) {
					$activation_key_storage_name = $provider_instance->get_activation_key_option_name( $provider, $license_id );
				}

				// Retrieve the activation key from WordPress options.
				$activation_key = get_option( $activation_key_storage_name );

				// Check if the activation key exists and if the license is valid.
				if ( $activation_key ) {
					$is_valid = $this->check_license_status( $plugin_slug, $activation_key );

					// Return the stored license data if the license is valid.
					if ( $is_valid ) {
						$storage_map[ $plugin_slug ]['licenseId'] = $license_id;
						return $storage_map[ $plugin_slug ];
					}
				}
			}
		}

		// If no valid license is found, send a request to provision a new license via the PLS API.
		$endpoint = '/sites/v2/pls/license';
		$body     = array(
			'pluginSlug'   => $plugin_slug,
			'providerName' => $provider,
		);

		// Send the API request to provision a new license.
		$hiive_request = new HiiveUtility( $endpoint, $body, 'POST' );
		$response      = $hiive_request->send_request();

		// Return an error if the API request fails.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		/**
		 * Parse the response and process the license storage data.
		 *
		 * @var array{storage_map?:array,license_id?:string,download_url:string,basename:string} $response_body
		 */
		$response_body = json_decode( $response, true );

		// If the storage map has values in the response, use them, otherwise fall back to Providers.
		$provider_instance = new Providers();

		/**
		 * Storage map response from API containing method, activation key, and license ID.
		 *
		 * @var array{method:string,activation_key?:string,license_id:string} $storage_map_response
		 */
		$storage_map_response = isset( $response_body['storage_map'] ) ? $response_body['storage_map'] : array();

		$activation_key_storage_name = ! empty( $storage_map_response['activation_key'] )
		? $storage_map_response['activation_key']
		: $provider_instance->get_activation_key_option_name( $provider, $response_body['license_id'] );

		$license_id_storage_name = ! empty( $storage_map_response['license_id'] )
		? $storage_map_response['license_id']
		: $provider_instance->get_license_id_option_name( $provider, $plugin_slug );

		$storage_method = ! empty( $storage_map_response['method'] )
		? $storage_map_response['method']
		: $provider_instance->get_storage_method( $provider );

		// Store the newly provisioned license ID in WordPress options.
		update_option( $license_id_storage_name, $response_body['license_id'] );

		// Prepare the new license storage map data.
		$storage_map_data = array(
			'downloadUrl'              => $response_body['download_url'],
			'basename'                 => $response_body['basename'],
			'provider'                 => $provider,
			'activationKeyStorageName' => $activation_key_storage_name,
			'licenseIdStorageName'     => $license_id_storage_name,
			'storageMethod'            => $storage_method,
		);

		// Save the new license storage map data.
		$storage_map[ $plugin_slug ] = $storage_map_data;
		$this->store_license_storage_map( $storage_map );

		// Return the newly provisioned license data.
		return array(
			'licenseId'                => $response_body['license_id'],
			'downloadUrl'              => $response_body['download_url'],
			'activationKeyStorageName' => $activation_key_storage_name,
			'licenseIdStorageName'     => $license_id_storage_name,
			'storageMethod'            => $storage_method,
		);
	}

	/**
	 * Activates a license by plugin slug via the PLS API.
	 * If an activation key exists and is valid, it returns it.
	 * Otherwise, it sends a request to the PLS API to activate the license.
	 *
	 * @param string $plugin_slug The plugin slug for which to activate the license.
	 * @return string|WP_Error Activation key or WP_Error on failure.
	 */
	public function activate_license( $plugin_slug ) {
		// Retrieve the stored license storage map for the plugin.
		$storage_map = $this->retrieve_license_storage_map();

		// Validate if a storage map exists for the provided plugin slug.
		if ( ! isset( $storage_map[ $plugin_slug ] ) ) {
			return new \WP_Error(
				'nfd_pls_error',
				__( 'No license storage map found for the specified plugin slug.', 'wp-module-pls' )
			);
		}

		$storage_data = $storage_map[ $plugin_slug ];

		// Retrieve the license ID and activation key storage names, or retrieve them from Providers if missing.
		if ( empty( $storage_data['activationKeyStorageName'] ) ) {
			if ( ! isset( $storage_data['provider'] ) ) {
				return new \WP_Error(
					'nfd_pls_error',
					__( 'Provider is not set in the storage data.', 'wp-module-pls' )
				);
			}

			$provider_instance = new Providers();

			// If licenseIdStorageName is not set, retrieve it using the provider and plugin slug.
			if ( empty( $storage_data['licenseIdStorageName'] ) ) {
				$storage_data['licenseIdStorageName'] = $provider_instance->get_license_id_option_name( $storage_data['provider'], $plugin_slug );
			}

			// Retrieve the license ID from WordPress options.
			$license_id = get_option( $storage_data['licenseIdStorageName'] );
			if ( ! $license_id ) {
				return new \WP_Error(
					'nfd_pls_error',
					__( 'License ID not found for the plugin.', 'wp-module-pls' )
				);
			}

			// Set the activationKeyStorageName if missing, using the provider and license ID.
			$storage_data['activationKeyStorageName'] = $provider_instance->get_activation_key_option_name( $storage_data['provider'], $license_id );
			// Update the storage map with the newly retrieved activationKeyStorageName.
			$storage_map[ $plugin_slug ] = $storage_data;
			$this->store_license_storage_map( $storage_map );
		}

		// Retrieve the activation key from WordPress options.
		$activation_key = get_option( $storage_data['activationKeyStorageName'] );
		// Validate the activation key if it exists.
		if ( $activation_key && $this->check_license_status( $plugin_slug, $activation_key ) ) {
			// Return the valid activation key.
			return $activation_key;
		}

		// If licenseIdStorageName is not set, retrieve it using the provider and plugin slug.
		if ( empty( $storage_data['licenseIdStorageName'] ) ) {
			$storage_data['licenseIdStorageName'] = $provider_instance->get_license_id_option_name( $storage_data['provider'], $plugin_slug );
		}
		// Retrieve the license ID from WordPress options.
		$license_id = get_option( $storage_data['licenseIdStorageName'] );
		if ( ! $license_id ) {
			return new \WP_Error(
				'nfd_pls_error',
				__( 'License ID not found for the plugin.', 'wp-module-pls' )
			);
		}

		// Prepare the request body for the activation API.
		$body = array(
			'domain_name' => get_home_url(),
			'email'       => get_option( 'admin_email' ),
		);

		// Send a request to the PLS API to activate the license.
		$response = wp_remote_post(
			"{$this->base_url}/license/{$license_id}/activate",
			array(
				'body'    => wp_json_encode( $body ),
				'headers' => array( 'Content-Type' => 'application/json' ),
				'timeout' => 30,
			)
		);

		// Handle errors in the API response.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code < 200 || $response_code >= 300 ) {
			return new \WP_Error(
				'nfd_pls_error',
				__( 'API returned a non-success status code: ', 'wp-module-pls' ) . $response_code
			);
		}

		// Parse the API response to get the activation key.
		$response_data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $response_data['data']['activation_key'] ) ) {
			// Store the activation key in WordPress options and return it.
			update_option( $storage_data['activationKeyStorageName'], $response_data['data']['activation_key'] );
			return $response_data['data']['activation_key'];
		}

		// Handle unexpected response formats from the API.
		return new \WP_Error(
			'nfd_pls_error',
			__( 'Unexpected response format from the API.', 'wp-module-pls' )
		);
	}

	/**
	 * Checks the status of the license activation by either plugin slug or activation key.
	 * If the activation key is not provided, it attempts to retrieve it using the plugin slug
	 * from the stored license map. If the activation key does not exist, it uses the provider's
	 * method to retrieve the appropriate option name.
	 *
	 * @param string|null $plugin_slug The slug of the plugin for which to check the activation status.
	 * @param string|null $activation_key The activation key, if available.
	 *
	 * @return bool True if the activation key is valid, false otherwise.
	 */
	public function check_license_status( $plugin_slug = null, $activation_key = null ) {
		// If the activation key is not provided, try to retrieve it using the plugin slug.
		if ( ! $activation_key && $plugin_slug ) {
			// Retrieve the stored license map.
			$storage_map = $this->retrieve_license_storage_map();

			// If no license storage map is found for the provided plugin slug, return false.
			if ( ! isset( $storage_map[ $plugin_slug ] ) ) {
				return false;
			}

			// Extract the storage data for the given plugin slug.
			$storage_data = $storage_map[ $plugin_slug ];

			// Retrieve the activation key storage name if missing, using Providers.
			if ( empty( $storage_data['activationKeyStorageName'] ) ) {
				if ( ! isset( $storage_data['provider'] ) ) {
					return false;
				}

				$provider_instance = new Providers();
				if ( empty( $storage_data['licenseIdStorageName'] ) ) {
					$storage_data['licenseIdStorageName'] = $provider_instance->get_license_id_option_name( $storage_data['provider'], $plugin_slug );
				}

				$license_id = get_option( $storage_data['licenseIdStorageName'] );
				if ( ! $license_id ) {
					return false;
				}

				$storage_data['activationKeyStorageName'] = $provider_instance->get_activation_key_option_name( $storage_data['provider'], $license_id );
				$storage_map[ $plugin_slug ]              = $storage_data;
				$this->store_license_storage_map( $storage_map );
			}

			// Retrieve the activation key from the stored option using the activation key storage name.
			$activation_key = get_option( $storage_data['activationKeyStorageName'] );

			// If the activation key is not found, return false.
			if ( ! $activation_key ) {
				return false;
			}
		}

		// Prepare the API request to check the activation key status.
		$response = wp_remote_get(
			"{$this->base_url}/license/{$activation_key}/status",
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'timeout' => 30,
			)
		);

		// Handle errors in the API response.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// Parse the response and return true if the activation key is valid.
		$response_data = json_decode( wp_remote_retrieve_body( $response ), true );
		return isset( $response_data['data']['valid'] ) && true === $response_data['data']['valid'];
	}
}
