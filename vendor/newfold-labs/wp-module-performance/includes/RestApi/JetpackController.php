<?php
namespace NewfoldLabs\WP\Module\Performance\RestApi;

use Automattic\Jetpack_Boost\Lib\Critical_CSS\Data_Sync_Actions\Regenerate_CSS;
use NewfoldLabs\WP\Module\Performance\Permissions;

/**
 * Class JetpackController
 *
 * @package NewfoldLabs\WP\Module\Performance
 */
class JetpackController {

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
	protected $rest_base = '/jetpack';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_slug = 'jetpack-boost';

	/**
	 * Register API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/settings',
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
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/regenerate_critical_css',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'regenerate_critical_css' ),
					'permission_callback' => array( Permissions::class, 'rest_is_authorized_admin' ),
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

			if ( 'critical-css' === $field['id'] && 1 === (int) $field['value'] && class_exists( 'Automattic\Jetpack_Boost\Lib\Critical_CSS\Data_Sync_Actions\Regenerate_CSS' ) ) {
				$css = new Regenerate_CSS();
				$css->handle( null, null );
			} elseif ( 'critical-css-premium' === $field['id'] ) {
				$field['id'] = 'critical-css';
			}

			$option_key   = 'jetpack_boost_status_' . $field['id'];
			$option_value = $field['value'];

			if ( in_array( $field['id'], array( 'minify-js-excludes', 'minify-css-excludes' ), true ) ) {
				$option_key   = 'jetpack_boost_ds_' . str_replace( '-', '_', $field['id'] );
				$option_value = explode( ',', $field['value'] );
			}

			$result = update_option( $option_key, $option_value );

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
					'updated_option' => $option_key,
					'updated_value'  => $option_value,
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

	/**
	 * Regenerate Critical CSS.
	 *
	 * @return \WP_REST_Response
	 */
	public function regenerate_critical_css() {
		try {
			$css    = new Regenerate_CSS();
			$result = $css->handle( null, null );

			if ( ! empty( $result['success'] ) && $result['success'] ) {
				// Success response.
				return new \WP_REST_Response(
					array(
						'success' => true,
					),
					200
				);
			} else {
				// Case where the process did not throw an exception but failed.
				return new \WP_REST_Response(
					array(
						'success' => false,
						'error'   => __( 'Failed to regenerate critical CSS.', 'wp-module-performance' ),
					),
					400 // Bad Request
				);
			}
		} catch ( \Exception $e ) {
			// Exception handling.
			return new \WP_REST_Response(
				array(
					'success' => false,
					'error'   => __( 'An error occurred while regenerating critical CSS.', 'wp-module-performance' ) . $e->getMessage(),
				),
				500 // Internal Server Error
			);
		}
	}
}
