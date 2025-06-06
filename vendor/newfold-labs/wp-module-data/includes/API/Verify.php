<?php

namespace NewfoldLabs\WP\Module\Data\API;

use NewfoldLabs\WP\Module\Data\HiiveConnection;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;

/**
 * REST API controller for verifying a hiive connection attempt
 */
class Verify extends WP_REST_Controller {

	/**
	 * Instance of HiiveConnection class
	 *
	 * @var HiiveConnection
	 */
	public $hiive;

	/**
	 * Constructor.
	 *
	 * @param HiiveConnection $hiive Instance of the hiive connection manager
	 * @since 4.7.0
	 */
	public function __construct( HiiveConnection $hiive ) {
		$this->hiive     = $hiive;
		$this->namespace = 'newfold-data/v1';
		$this->rest_base = 'verify';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 4.7.0
	 *
	 * @see register_rest_route()
	 * @see HiiveConnection::rest_api_init()
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<token>[a-f0-9]{32})',
			array(
				'args' => array(
					'token' => array(
						'description' => __( 'Connection verification token.' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Returns a verification of the supplied connection token
	 *
	 * @since 1.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$valid  = $this->hiive->verify_token( $request['token'] );
		$status = ( $valid ) ? 200 : 401;

		$response = new WP_REST_Response(
			array(
				'token' => $request['token'],
				'valid' => $valid,
			),
			$status
		);

		return $response;
	}
}
