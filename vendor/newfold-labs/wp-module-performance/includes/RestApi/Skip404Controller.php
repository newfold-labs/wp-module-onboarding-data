<?php
namespace NewfoldLabs\WP\Module\Performance\RestApi;

use NewfoldLabs\WP\Module\Performance\Skip404\Skip404;

/**
 * Class Settings
 *
 * @package NewfoldLabs\WP\Module\Performance
 */
class Skip404Controller {

	/**
	 * The REST route namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'newfold-performance/v1';

	/**
	 * The REST route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/skip404';

	/**
	 * Register API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_options' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				),
			)
		);
	}

	/**
	 * Set Jetpack options.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public function set_options( $request ) {
		try {
			$params = $request->get_params();

			if ( ! isset( $params['field'] ) || ! is_array( $params['field'] ) ) {
				return new \WP_REST_Response(
					array(
						'success' => false,
						'error'   => __( "The parameter 'field' is missing or invalid.", 'wp-module-performance' ),
					),
					400
				);
			}

			$field = $params['field'];

			if ( ! isset( $field['id'], $field['value'] ) ) {
				return new \WP_REST_Response(
					array(
						'success' => false,
						'error'   => __( "The fields 'id' and 'value' are required.", 'wp-module-performance' ),
					),
					400
				);
			}

			switch ( $field['id'] ) {
				case 'skip404':
					$result = update_option( Skip404::OPTION_NAME, $field['value'] );
					break;

				default:
					break;
			}

			if ( false === $result ) {
				return new \WP_REST_Response(
					array(
						'success' => false,
						'error'   => __( 'An error occurred while updating the option.', 'wp-module-performance' ),
					),
					500
				);
			}

			// Success response.
			return new \WP_REST_Response(
				array(
					'success'        => true,
					'updated_option' => $field['id'],
					'updated_value'  => $field['value'],
				),
				200
			);
		} catch ( \Exception $e ) {
			// Exceptions handling.
			return new \WP_REST_Response(
				array(
					'success' => false,
					'error'   => __( 'An error occurred while updating the option.', 'wp-module-performance' ) . $e->getMessage(),
				),
				500
			);
		}
	}
}
