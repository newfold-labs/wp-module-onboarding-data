<?php

namespace NewfoldLabs\WP\Module\Features;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use function NewfoldLabs\WP\Module\Features\enable;
use function NewfoldLabs\WP\Module\Features\disable;
use function NewfoldLabs\WP\Module\Features\isEnabled;

/**
 * Class FeaturesAPI
 */
class FeaturesAPI extends WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = 'newfold-features/v1';

	/**
	 * An instance of the Features class.
	 *
	 * @var Features
	 */
	protected $features;

	/**
	 * FeaturesApi Controller constructor.
	 */
	public function __construct() {
		$this->features = Features::getInstance();
		$this->register_routes();
	}

	/**
	 * Register API Routes
	 */
	public function register_routes() {

		// Get all features endpoint
		register_rest_route(
			$this->namespace,
			'/features',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'features' ),
				'permission_callback' => array( $this, 'checkPermission' ),
			)
		);

		// Register feature enable endpoint
		register_rest_route(
			$this->namespace,
			'/feature/enable',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'featureEnable' ),
				'permission_callback' => array( $this, 'checkPermission' ),
				'args'                => array(
					'feature' => array(
						'required'          => true,
						'validate_callback' => array( $this, 'validateFeatureParam' ),
					),
				),
			)
		);

		// Register feature disable endpoint
		register_rest_route(
			$this->namespace,
			'/feature/disable',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'featureDisable' ),
				'permission_callback' => array( $this, 'checkPermission' ),
				'args'                => array(
					'feature' => array(
						'required'          => true,
						'validate_callback' => array( $this, 'validateFeatureParam' ),
					),
				),
			)
		);

		// Register feature is enabled check endpoint
		register_rest_route(
			$this->namespace,
			'/feature/isEnabled',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'featureIsEnabled' ),
				'permission_callback' => array( $this, 'checkPermission' ),
				'args'                => array(
					'feature' => array(
						'required'          => true,
						'validate_callback' => array( $this, 'validateFeatureParam' ),
					),
				),
			)
		);
	}

	/**
	 * Callback to validate feature exists
	 *
	 * @param string          $param the parameter
	 * @param WP_REST_Request $request the request
	 * @param string          $key the key
	 * @return bool
	 */
	public function validateFeatureParam( $param, $request, $key ) {
		return $this->features->hasFeature( $param );
	}

	/**
	 * Check permissions for routes.
	 * Always returns true since permissions are managed in the specific Feature classes
	 *
	 * @return bool
	 */
	public function checkPermission() {
		return true;
	}

	/**
	 * Get features via REST API.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response object.
	 */
	public function features( WP_REST_Request $request ) {
		return new WP_REST_Response(
			array(
				'features' => $this->features->getFeatures(),
			),
			200
		);
	}

	/**
	 * Callback to enable a feature via REST API.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error The response object or WP_Error on failure.
	 */
	public function featureEnable( WP_REST_Request $request ) {
		$name   = $request->get_param( 'feature' );
		$result = enable( $name );

		// success
		if ( $result ) {
			return new WP_REST_Response(
				isEnabled( $name ), // verifying enable was successful since actions could override
				200
			);
		}

		// else other error, typically permissions
		return new WP_Error(
			'nfd_features_error',
			'Cannot modify this feature.',
			array( 'status' => 403 )
		);
	}

	/**
	 * Callback to disable a feature via REST API.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error The response object or WP_Error on failure.
	 */
	public function featureDisable( WP_REST_Request $request ) {
		$name   = $request->get_param( 'feature' );
		$result = disable( $name );

		// success
		if ( $result ) {
			return new WP_REST_Response(
				! isEnabled( $name ), // verifying enable was successful since actions could override
				200
			);
		}

		// else other error, typically permissions
		return new WP_Error(
			'nfd_features_error',
			'Cannot modify this feature.',
			array( 'status' => 403 )
		);
	}

	/**
	 * Callback to check if a feature is enabled via REST API.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response object.
	 */
	public function featureIsEnabled( WP_REST_Request $request ) {
		$name   = $request['feature'];
		$result = isEnabled( $name );

		return new WP_REST_Response(
			$result,
			200
		);
	}
}
