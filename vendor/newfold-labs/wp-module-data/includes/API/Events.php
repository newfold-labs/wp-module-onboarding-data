<?php

namespace NewfoldLabs\WP\Module\Data\API;

use NewfoldLabs\WP\Module\Data\Event;
use NewfoldLabs\WP\Module\Data\EventManager;
use NewfoldLabs\WP\Module\Data\HiiveConnection;
use WP_REST_Controller;
use WP_REST_Server;

/**
 * REST API controller for sending events to the hiive.
 */
class Events extends WP_REST_Controller {

	/**
	 * Instance of the EventManager class.
	 *
	 * @var EventManager
	 */
	public $event_manager;

	/**
	 * Instance of the HiiveConnection class.
	 *
	 * @var HiiveConnection
	 */
	public $hiive;

	/**
	 * Events constructor.
	 *
	 * @param HiiveConnection $hiive           Instance of the HiiveConnection class.
	 * @param EventManager    $event_manager Instance of the EventManager class.
	 */
	public function __construct( HiiveConnection $hiive, EventManager $event_manager ) {
		$this->event_manager = $event_manager;
		$this->hiive         = $hiive;
		$this->namespace     = 'newfold-data/v1';
		$this->rest_base     = 'events';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @see register_rest_route()
	 * @see EventManager::rest_api_init()
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/',
			array(
				'args' => array(
					'action'   => array(
						'required'          => true,
						'description'       => __( 'Event action. For the "pageview" action/key, Hiive tries to read the page URL and page title only from the "page" and "page_title" keys in the data arg.' ),
						'type'              => 'string',
						'sanitize_callback' => function ( $value ) {
							return sanitize_title( $value );
						},
					),
					'category' => array(
						'default'           => 'admin',
						'description'       => __( 'Event category' ),
						'type'              => 'string',
						'sanitize_callback' => function ( $value ) {
							return sanitize_title( $value );
						},
					),
					'data'     => array(
						'description' => __( 'Event data' ),
						'type'        => 'object',
					),
					'queue'    => array(
						'default'           => true,
						'description'       => __( 'Whether or not to queue the event' ),
						'type'              => 'boolean',
						'sanitize_callback' => function ( $value ) {
							return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
						},
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
				),
			)
		);

		\register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/batch',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_items' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Dispatches a new event.
	 *
	 * `wp-json/newfold-data/v1/events`
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @used-by newfold-notifications/v1/notifications/
	 * @used-by NotificationsApi::registerRoutes() (in callback)
	 * @used-by wp-module-notifications/assets/js/realtime-notices.js:189
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {

		$category = $request->get_param( 'category' );
		$action   = $request->get_param( 'action' );
		$data     = ! empty( $request['data'] ) ? $request['data'] : array();

		$event = new Event( $category, $action, $data );

		// If request isn't to be queued, we want the realtime response.
		if ( ! $request['queue'] ) {
			$hiive_response_notifications = $this->hiive->send_event( $event );

			if ( is_wp_error( $hiive_response_notifications ) ) {
				return new \WP_REST_Response( $hiive_response_notifications->get_error_message(), 500 );
			}

			return new \WP_REST_Response( array( 'data' => $hiive_response_notifications ), 201 );
		}

		// Otherwise, queue the event.
		$this->event_manager->push( $event );

		$response = rest_ensure_response(
			array(
				'category' => $category,
				'action'   => $action,
				'data'     => $data,
			)
		);
		// 202 – "The request has been accepted for processing, but the processing has not been completed.".
		$response->set_status( 202 );

		return $response;
	}

	/**
	 * User is required to be logged in.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error
	 *
	 * @since 1.0
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return new \WP_Error(
				'rest_cannot_log_event',
				__( 'Sorry, you are not allowed to use this endpoint.' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Manages sending a batch of events to the single event API.
	 *
	 * @param \WP_REST_Request $request A request containing an array of events.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_items( $request ) {
		$events = $request->get_json_params();
		if ( ! rest_is_array( $events ) ) {
			return new \WP_Error(
				'rest_cannot_log_events',
				__( 'Request does not contain an array of events.' )
			);
		}

		$errors = array();
		foreach ( $events as $index => $event ) {
			$event_request = new \WP_REST_Request(
				\WP_REST_Server::CREATABLE,
				"/{$this->namespace}/{$this->rest_base}"
			);
			$event_request->set_body_params( $event );
			$response = \rest_do_request( $event_request );
			if ( $response->is_error() ) {
				array_push(
					$errors,
					array(
						'index' => $index,
						'data'  => $response->as_error(),
					)
				);
			}
		}

		if ( ! empty( $errors ) ) {
			return new \WP_Error(
				'rest_cannot_log_events',
				__( 'Some events failed.' ),
				array(
					'errors' => $errors,
				)
			);
		}

		return new \WP_REST_Response(
			array(),
			202 // Accepted. The request has been accepted for processing, but the processing has not been completed.
		);
	}
}
