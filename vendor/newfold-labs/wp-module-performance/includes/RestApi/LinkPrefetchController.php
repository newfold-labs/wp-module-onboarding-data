<?php

namespace NewfoldLabs\WP\Module\Performance\RestApi;

use NewfoldLabs\WP\Module\Performance\LinkPrefetch\LinkPrefetch;

/**
 * Class LinkPrefetchController
 */
class LinkPrefetchController {

	/**
	 * REST namespace
	 *
	 * @var string
	 */
	protected $namespace = 'newfold-performance/v1';

	/**
	 * REST base
	 *
	 * @var string
	 */
	protected $rest_base = '/link-prefetch';

	/**
	 * Registers rest routes for PluginsController class.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/settings',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				),
			)
		);
	}

	/**
	 * Get the settings
	 *
	 * @return \WP_REST_Response
	 */
	public function get_settings() {
		return new \WP_REST_Response(
			array(
				'settings' => LinkPrefetch::get_settings(),
			),
			200
		);
	}

	/**
	 * Update the settings
	 *
	 * @param \WP_REST_Request $request the request.
	 * @return \WP_REST_Response
	 */
	public function update_settings( \WP_REST_Request $request ) {
		$settings = $request->get_param( 'settings' );
		if ( is_array( $settings ) ) {
			$settings['ignoreKeywords'] = sanitize_text_field( $settings['ignoreKeywords'] );
			$updated                    = LinkPrefetch::update_settings( $settings );
			return new \WP_REST_Response(
				array(
					'result' => $updated,
				),
				$updated ? 200 : 400
			);
		}

		return new \WP_REST_Response(
			array(
				'result'  => false,
				'message' => 'Invalid settings format',
			),
			400
		);
	}
}
