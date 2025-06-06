<?php

namespace NewfoldLabs\WP\Module\Performance\Images\RestApi;

use NewfoldLabs\WP\Module\Performance\Images\ImageService;
use NewfoldLabs\WP\Module\Performance\Images\ImageSettings;

/**
 * Class ImagesController
 * Provides REST API for single media item optimization.
 */
class ImagesController {

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
	protected $rest_base = '/images';

	/**
	 * Registers API routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/optimize',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'optimize_image' ),
				'permission_callback' => function () {
					return current_user_can( 'upload_files' );
				},
				'args'                => array(
					'media_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'description'       => __( 'The ID of the media item to optimize.', 'wp-module-performance' ),
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && $param > 0;
						},
					),
				),
			)
		);
	}

	/**
	 * Optimizes a single media item.
	 *
	 * @param \WP_REST_Request $request The REST API request.
	 * @return \WP_REST_Response
	 */
	public function optimize_image( \WP_REST_Request $request ) {
		$media_id = $request->get_param( 'media_id' );

		$file_path = get_attached_file( $media_id );
		$image_url = wp_get_attachment_url( $media_id );

		if ( empty( $file_path ) || empty( $image_url ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'error'   => __( 'Invalid media ID or media item not found.', 'wp-module-performance' ),
				),
				400
			);
		}

		$image_service    = new ImageService();
		$delete_original  = ImageSettings::is_auto_delete_enabled();
		$optimized_result = $image_service->optimize_image( $image_url, $file_path );

		if ( is_wp_error( $optimized_result ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'error'   => $optimized_result->get_error_message(),
				),
				500
			);
		}

		if ( $delete_original ) {
			$response = $image_service->replace_original_with_webp( $media_id, $optimized_result );
		} else {
			$response = $image_service->register_webp_as_new_media( $optimized_result );
		}

		if ( is_wp_error( $response ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'error'   => $response->get_error_message(),
				),
				500
			);
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Image successfully optimized.', 'wp-module-performance' ),
			),
			200
		);
	}
}
