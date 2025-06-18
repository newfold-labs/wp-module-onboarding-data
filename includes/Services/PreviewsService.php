<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

/**
 * Class for generating snapshots.
 */
class PreviewsService {
	/**
	 * The API that processes block rendering.
	 *
	 * @var string
	 */
	protected static $screenshot_service_api = 'https://hiive.cloud/workers/screenshot-service/';

	/**
	 * Generate the snapshot.
	 * 
	 * @param string $content The content to render.
	 * @param string $slug The slug of the page.
	 * @param string $custom_styles The custom styles to apply to the page.
	 * @return array|\WP_Error
	 */
	public static function generate_snapshot( $content, $slug, $custom_styles = null ): array | \WP_Error {
		if ( ! self::validate( $content ) ) {
			return new \WP_Error( 400, 'Invalid pattern content.', array( 'status' => 400 ) );
		}

		// Initial response array.
		$response = [
			'screenshot' => null,
			'post_url'   => null,
			'post_id'    => null,
		];
		
		// Publish the page (to be crawled by the screenshot service or used as a fallback iframe).
		$post                 = self::publish_page( $content, $slug, $custom_styles );
		$response['post_url'] = $post['post_url'];
		$response['post_id']  = $post['post_id'];
		
		// Generate the screenshot.
		$screenshot = self::capture_screenshot( url: $post['post_url'], key: $slug );
		if ( ! is_wp_error( $screenshot ) ) {
			$response['screenshot'] = $screenshot;
		}

		return $response;
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
	 * Publish the page.
	 * 
	 * @param string $content The content to render.
	 * @param string $slug The slug of the page.
	 * @return array
	 */
	private static function publish_page( string $content, string $slug, $custom_styles = null ): array {
		// Inject custom styles if provided
		if ( $custom_styles ) {
			$styles = '<style>.entry-content .nfd-container:first-of-type {margin-top: 0 !important;}';
			$styles .= $custom_styles;
			$styles .= '</style>';
		}

		// Inject iframe script
		$iframe_script = '<script>
			document.addEventListener("DOMContentLoaded", function() {
				// Check if page is loaded in an iframe
				if (typeof window !== "undefined" && window.self !== window.top) {
					// Hide the admin bar
					const adminBar = document.getElementById("wpadminbar");
					if (adminBar) {
						adminBar.style.display = "none";
						// Remove the admin bar reserved space
						document.documentElement.style.setProperty("margin-top", "0px", "important");
					}

					// Prevent click events
					document.addEventListener("click", function(e) {
						e.preventDefault();
						e.stopPropagation();
						return false;
					}, true);

					// Prevent context menu (right click)
					document.addEventListener("contextmenu", function(e) {
						e.preventDefault();
						return false;
					});
				}
			});
		</script>';

		$post_id = wp_insert_post( array(
			'post_title'    => 'Home-' . $slug,
			'post_name'     => 'home-' . $slug,
			'post_content'  => $styles . $iframe_script . $content,
			'post_status'   => 'publish',
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
	 * Generate the screenshot.
	 * 
	 * @link https://hiive.cloud/workers/screenshot-service/ documentation.
	 * @param string $url The URL of the page.
	 * @param string $key The key of the page.
	 * @return string|WP_Error
	 */
	public static function capture_screenshot( string $url, string $key ): string | \WP_Error {
		$body = array(
			'url'   => $url,
			'key'  => $key,
			'quality' => 50,
		);
		$args = array(
			'body'    => wp_json_encode( $body ),
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'timeout' => 30,
		);

		$response = wp_remote_post( self::$screenshot_service_api, $args );
		$status   = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Unable to generate screenshot.', 'wp-module-onboarding-data' ),
				array(
					'status' => '500',
				)
			);
		}

		// Get the image data and convert it to base64.
		$imgBinary = wp_remote_retrieve_body( $response );
		$imgBase64 = base64_encode( $imgBinary );
		$img = 'data:image/webp;base64,' . $imgBase64;
		return $img;
	}
}
