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
	protected static $screenshot_service_api = 'https://sw296hy87g.execute-api.us-west-2.amazonaws.com/default';

	/**
	 * Generate the renderable content.
	 * 
	 * @param string $content The content to render.
	 * @param string $slug The slug of the page.
	 * @return array|\WP_Error
	 */
	public static function generate_renderable_content( $content, $slug ): array | \WP_Error {
		if ( ! self::validate( $content ) ) {
			return new \WP_Error( 400, 'Invalid pattern content.', array( 'status' => 400 ) );
		}
		
		$url = self::generate_renderable_url( $content, $slug );
		return $url;
	}
	
	/**
	 * Validate the pattern content.
	 * 
	 * @param string $content The content to validate.
	 * @return bool
	 */
	private static function validate( string $content ): bool {
		$blocks_content = $content;

		try {
			// Parse and render the pattern.
			$parsed = parse_blocks( $blocks_content );
			if ( ! isset( $parsed[0] ) || empty($parsed[0] ) ) {
				throw new \Exception( 'Invalid pattern content.' );
			}
			render_block( $parsed[0] );

			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Generate the renderable URL.
	 * 
	 * @param string $content The content to render.
	 * @param string $slug The slug of the page.
	 * @return array
	 */
	private static function generate_renderable_url( string $content, string $slug ): array {
		$post_id = wp_insert_post( array(
			'post_title'    => 'Home-' . $slug,
			'post_name'     => 'home-' . $slug,
			'post_content'  => $content,
			'post_status'   => 'draft',
			'post_type'     => 'page',
			'page_template' => 'blank',
		) );

		$post_url = get_permalink( $post_id );

		return [
			'post_url' => $post_url,
			'post_id' => $post_id,
		];
	}

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

		$response = wp_remote_post( self::$screenshot_service_api . '/block-render-service', $args );
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
