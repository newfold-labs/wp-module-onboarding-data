<?php

namespace NewfoldLabs\WP\Module\PLS\RestApi\Controllers;

use NewfoldLabs\WP\Module\PLS\Permissions;
use NewfoldLabs\WP\Module\PLS\Utilities\PLSUtility;

/**
 * Class PLSController
 *
 * Handles the PLS REST API integration.
 */
class PLSController extends \WP_REST_Controller {

	/**
	 * The namespace for the REST API endpoint.
	 *
	 * @var string
	 */
	protected $namespace = 'newfold-pls/v1';

	/**
	 * The base for the REST API endpoint.
	 *
	 * @var string
	 */
	protected $rest_base = 'license';

	/**
	 * Instance of the PLSUtility class.
	 *
	 * @var PLSUtility
	 */
	protected $pls_utility;

	/**
	 * Constructor for the PLSController class.
	 */
	public function __construct() {
		// Instantiate PLSUtility for license handling
		$this->pls_utility = new PLSUtility();
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		// Route to request a new license
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_license' ),
				'permission_callback' => array( Permissions::class, 'rest_is_authorized_admin' ),
				'args'                => $this->get_args(),
			)
		);

		// Route to activate an existing license
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/activate',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'activate_license' ),
				'permission_callback' => array( Permissions::class, 'rest_is_authorized_admin' ),
				'args'                => $this->get_args(),
			)
		);

		// Route to check license status
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/status',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_license_status' ),
				'permission_callback' => array( Permissions::class, 'rest_is_authorized_admin' ),
				'args'                => $this->get_args(),
			)
		);
	}

	/**
	 * Arguments required for the PLS API endpoints.
	 *
	 * @return array
	 */
	public function get_args() {
		return array(
			'pluginSlug' => array(
				'required'          => true,
				'validate_callback' => function ( $param ) {
					return is_string( $param ) && ! empty( $param );
				},
			),
		);
	}

	/**
	 * Callback to create a new license.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_license( $request ) {
		$plugin_slug = sanitize_text_field( $request->get_param( 'pluginSlug' ) );

		// Use the instance of PLSUtility to provision a new license
		$response = $this->pls_utility->provision_license( $plugin_slug );

		if ( is_wp_error( $response ) ) {
			return new \WP_REST_Response(
				array(
					'error' => $response->get_error_message(),
				),
				400
			);
		}

		return new \WP_REST_Response( $response, 201 );
	}

	/**
	 * Callback to retrieve the license status.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_license_status( $request ) {
		$plugin_slug = sanitize_text_field( $request->get_param( 'pluginSlug' ) );

		// Use the instance of PLSUtility to retrieve license status
		$license_status = $this->pls_utility->retrieve_license_status( $plugin_slug );
		if ( is_wp_error( $license_status ) ) {
			return new \WP_REST_Response(
				array(
					'error' => $license_status->get_error_message(),
				),
				400
			);
		}

		return new \WP_REST_Response( array( 'status' => $license_status ), 200 );
	}

	/**
	 * Callback to activate an existing license.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 *
	 * @return \WP_REST_Response|WP_Error
	 */
	public function activate_license( $request ) {
		$plugin_slug = sanitize_text_field( $request->get_param( 'pluginSlug' ) );

		// Use the instance of PLSUtility to activate the license
		$activation_result = $this->pls_utility->activate_license( $plugin_slug );
		if ( is_wp_error( $activation_result ) ) {
			return new \WP_REST_Response(
				array(
					'error' => $activation_result->get_error_message(),
				),
				400
			);
		}

		return new \WP_REST_Response( $activation_result, 200 );
	}
}
