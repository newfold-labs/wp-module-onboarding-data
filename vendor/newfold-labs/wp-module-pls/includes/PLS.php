<?php

namespace NewfoldLabs\WP\Module\PLS;

use NewfoldLabs\WP\Module\PLS\RestApi\RestApi;
use NewfoldLabs\WP\Module\PLS\Utilities\HiiveUtility;
use NewfoldLabs\WP\Module\PLS\Utilities\PLSUtility;
use NewfoldLabs\WP\Module\PLS\WPCLI\WPCLI;
use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * Manages all the functionalities for the module.
 */
class PLS {
	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor for the PLS class.
	 *
	 * @param Container $container The module container.
	 */
	public function __construct( Container $container ) {
		// We're trying to avoid adding more stuff to this.
		$this->container = $container;

		\add_action( 'init', array( __CLASS__, 'load_text_domain' ), 100 );
		\add_filter( 'upgrader_package_options', array( __CLASS__, 'filter_upgrader_package_options' ) );

		if ( Permissions::rest_is_authorized_admin() ) {
			new RestApi();
		}

		new WPCLI();
	}


	/**
	 * Load text domain for Module
	 *
	 * @return void
	 */
	public static function load_text_domain() {

		\load_plugin_textdomain(
			'wp-module-pls',
			false,
			NFD_PLS_DIR . '/languages'
		);
	}

	/**
	 * Filter upgrader package options to append license_key and activation_key
	 *
	 * @param array $options The package options array.
	 * @return array
	 */
	public static function filter_upgrader_package_options( $options ) {

		// Get the plugin path.
		$plugin_path = $options['hook_extra']['plugin'] ?? '';
		if ( empty( $plugin_path ) ) {
			return $options;
		}

		// Get the plugin slug.
		$data = get_file_data( WP_PLUGIN_DIR . '/' . $plugin_path, array( 'slug' => 'BH Slug' ) );
		if ( empty( $data['slug'] ) ) {
			return $options;
		}

		$slug  = $data['slug'];
		$hiive = new HiiveUtility( "/releases/v1/plugins/{$slug}/download" );

		$pls_utility     = new PLSUtility();
		$license_mapping = $pls_utility->retrieve_license_storage_map();

		$options['package'] = add_query_arg(
			array(
				'license-key'    => isset( $license_mapping[ $slug ] ) ? get_option( $license_mapping[ $slug ]['licenseIdStorageName'], '' ) : '',
				'activation-key' => isset( $license_mapping[ $slug ] ) ? get_option( $license_mapping[ $slug ]['activationKeyStorageName'], '' ) : '',
			),
			$hiive->get_request_url()
		);

		return $options;
	}
}
