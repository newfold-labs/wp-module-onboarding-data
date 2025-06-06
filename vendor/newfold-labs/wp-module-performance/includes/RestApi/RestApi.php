<?php
namespace NewfoldLabs\WP\Module\Performance\RestApi;

/**
 * Instantiate controllers and register routes.
 */
final class RestApi {

	/**
	 * List of custom REST API controllers
	 *
	 * @var array
	 */
	protected $controllers = array(
		'NewfoldLabs\\WP\\Module\\Performance\\RestApi\\Skip404Controller',
		'NewfoldLabs\\WP\\Module\\Performance\\RestApi\\LinkPrefetchController',
		'NewfoldLabs\\WP\\Module\\Performance\\RestApi\\JetpackController',
		'NewfoldLabs\\WP\\Module\\Performance\\RestApi\\CacheController',
	);

	/**
	 * Setup the custom REST API
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the custom REST API routes
	 */
	public function register_routes() {
		foreach ( $this->controllers as $controller ) {
			/**
			 * Get an instance of the WP_REST_Controller.
			 *
			 * @var $instance WP_REST_Controller
			 */
			$instance = new $controller();
			$instance->register_routes();
		}
	}
}
