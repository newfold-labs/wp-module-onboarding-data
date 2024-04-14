<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

/**
 * Class for rendering blocks.
 */
class BlockRenderService {
	/**
	 * The API that processes block rendering.
	 *
	 * @var string
	 */
	protected static $api = 'https://sw296hy87g.execute-api.us-west-2.amazonaws.com/default';
	/**
	 * Render Block HTML and takes a PNG screenshot.
	 *
	 * @param integer $width The width of the PNG.
	 * @param integer $height The height of the PNG.
	 * @param string  $content The block HTML to render.
	 * @return \WP_Error|array
	 */
	public static function generate_screenshot( $width, $height, $content ) {
		$body = array(
			'width'   => $width,
			'height'  => $height,
			'content' => $content,
		);
		$args = array(
			'body'    => wp_json_encode( $body ),
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'timeout' => 30,
		);

		$response = wp_remote_post( self::$api . '/block-render-service', $args );
		$status   = wp_remote_retrieve_response_code( $response );

		if ( 201 !== $status ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Unable to generate screenshot.', 'wp-module-onboarding-data' ),
				array(
					'status' => '500',
				)
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );
		if ( empty( $body->screenshot ) ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Unable to generate screenshot.', 'wp-module-onboarding-data' ),
				array(
					'status' => '500',
				)
			);
		}

		return array(
			'screenshot' => $body->screenshot,
		);
	}
}
