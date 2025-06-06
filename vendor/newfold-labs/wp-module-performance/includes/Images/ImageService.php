<?php

namespace NewfoldLabs\WP\Module\Performance\Images;

use NewfoldLabs\WP\Module\Performance\Data\Events;
use NewfoldLabs\WP\Module\Performance\Services\EventService;

/**
 * Optimizes images using a Cloudflare Worker and saves them locally.
 */
class ImageService {

	/**
	 * Cloudflare Worker URL for image optimization.
	 */
	private const WORKER_URL = 'https://hiive.cloud/workers/image-optimization';

	/**
	 * Rate limit transient key.
	 *
	 * @var string
	 */
	public static $rate_limit_transient_key = 'nfd_image_optimization_rate_limit';

	/**
	 * Optimizes an uploaded image by sending it to the Cloudflare Worker and saving the result as WebP.
	 *
	 * @param string $image_url The URL of the uploaded image.
	 * @param string $original_file_path The original file path of the uploaded image.
	 * @return string|WP_Error The path to the optimized WebP file or a WP_Error on failure.
	 */
	public function optimize_image( $image_url, $original_file_path ) {
		// Validate the image URL
		if ( empty( $image_url ) || ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			return new \WP_Error(
				'nfd_performance_error',
				__( 'The provided image URL is invalid.', 'wp-module-performance' )
			);
		}

		$site_url = get_site_url();
		if ( ! $site_url ) {
			return new \WP_Error(
				'nfd_performance_error',
				__( 'Error retrieving site URL.', 'wp-module-performance' )
			);
		}

		// Check if the site is permanently banned
		if ( ImageSettings::is_banned() ) {
			return new \WP_Error(
				'nfd_performance_error',
				__( 'This site no longer qualifies for image optimization as it has reached its usage limits.', 'wp-module-performance' )
			);
		}

		// Check for rate limiting
		$rate_limit_transient = get_transient( self::$rate_limit_transient_key );
		if ( $rate_limit_transient ) {
			return new \WP_Error(
				'nfd_performance_error',
				sprintf(
				/* translators: %s: Retry time in seconds */
					__( 'This site has made too many requests in a short period. Please wait %s before trying again.', 'wp-module-performance' ),
					human_time_diff( time(), $rate_limit_transient )
				)
			);
		}

		EventService::send(
			array(
				'category' => Events::get_category()[0],
				'action'   => 'image_transformation_requested',
				'data'     => array(
					'image_url' => $image_url,
				),
			)
		);

		// Make a POST request to the Cloudflare Worker
		$response = wp_remote_post(
			self::WORKER_URL . '/?image=' . rawurlencode( $image_url ),
			array(
				'method'  => 'POST',
				'timeout' => 30,
				'headers' => array(
					'X-Site-Url' => $site_url,
				),
			)
		);

		// Update the stored monthly usage data.
		$monthly_request_count = wp_remote_retrieve_header( $response, 'X-Monthly-Request-Count' );
		$monthly_limit         = wp_remote_retrieve_header( $response, 'X-Monthly-Limit' );
		$monthly_request_count = ( '' !== $monthly_request_count ) ? intval( $monthly_request_count ) : null;
		$monthly_limit         = ( '' !== $monthly_limit ) ? intval( $monthly_limit ) : null;
		if ( null !== $monthly_request_count && null !== $monthly_limit ) {
			$settings                  = ImageSettings::get();
			$settings['monthly_usage'] = array(
				'monthlyRequestCount' => $monthly_request_count,
				'maxRequestsPerMonth' => $monthly_limit,
			);
			ImageSettings::update( $settings );
		}

		// Handle errors from the HTTP request
		if ( is_wp_error( $response ) ) {
			EventService::send(
				array(
					'category' => Events::get_category()[0],
					'action'   => 'image_transformation_failed',
					'data'     => array(
						'image_url' => $image_url,
						'error'     => $response->get_error_message(),
					),
				)
			);
			return new \WP_Error(
				'nfd_performance_error',
				sprintf(
				/* translators: %s: Error message */
					__( 'Error connecting to Cloudflare Worker: %s', 'wp-module-performance' ),
					$response->get_error_message()
				)
			);
		}

		// Check for HTTP errors
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 403 === $response_code ) {
			// If worker indicates a permanent ban, ban the site
			$this->ban_site();
			EventService::send(
				array(
					'category' => Events::get_category()[0],
					'action'   => 'image_transformation_failed',
					'data'     => array(
						'image_url' => $image_url,
						'error'     => __( 'Image optimization access has been permanently revoked for this site.', 'wp-module-performance' ),
					),
				)
			);
			return new \WP_Error(
				'nfd_performance_error',
				__( 'Image optimization access has been permanently revoked for this site.', 'wp-module-performance' )
			);
		} elseif ( 429 === $response_code ) {
			// Set a transient for the retry period
			$retry_after   = wp_remote_retrieve_header( $response, 'Retry-After' );
			$retry_seconds = $retry_after ? intval( $retry_after ) : 60;
			set_transient( self::$rate_limit_transient_key, time() + $retry_seconds, $retry_seconds );
			EventService::send(
				array(
					'category' => Events::get_category()[0],
					'action'   => 'image_transformation_failed',
					'data'     => array(
						'image_url' => $image_url,
						'error'     => __( 'Rate limit exceeded. Please try again later.', 'wp-module-performance' ),
					),
				)
			);
			return new \WP_Error(
				'nfd_performance_error',
				__( 'Rate limit exceeded. Please try again later.', 'wp-module-performance' )
			);
		}

		$optimized_image_body = wp_remote_retrieve_body( $response );
		$content_type         = wp_remote_retrieve_header( $response, 'content-type' );
		if ( empty( $optimized_image_body ) || 'image/webp' !== $content_type ) {
			$error_message = $this->get_response_message( $response ) ?? __( 'Invalid response from Cloudflare Worker.', 'wp-module-performance' );
			EventService::send(
				array(
					'category' => Events::get_category()[0],
					'action'   => 'image_transformation_failed',
					'data'     => array(
						'image_url' => $image_url,
						'error'     => $error_message,
					),
				)
			);
			return new \WP_Error(
				'nfd_performance_error',
				$error_message
			);
		}

		EventService::send(
			array(
				'category' => Events::get_category()[0],
				'action'   => 'image_transformation_completed',
				'data'     => array(
					'image_url' => $image_url,
				),
			)
		);

		// Save the WebP image to the same directory as the original file
		$webp_file_path = $this->generate_webp_file_path( $original_file_path );
		if ( is_wp_error( $webp_file_path ) ) {
			return $webp_file_path;
		}
		if ( true !== $this->save_file( $webp_file_path, $optimized_image_body ) ) {
			return new \WP_Error(
				'nfd_performance_error',
				__( 'Failed to save the optimized WebP image.', 'wp-module-performance' )
			);
		}

		return $webp_file_path;
	}

	/**
	 * Permanently ban the site from accessing image optimization.
	 */
	private function ban_site() {
		$settings                      = ImageSettings::get();
		$settings['banned_status']     = true;
		$settings['bulk_optimization'] = false;
		$settings['auto_optimized_uploaded_images']['enabled']                    = false;
		$settings['auto_optimized_uploaded_images']['auto_delete_original_image'] = false;
		ImageSettings::update( $settings );
	}

	/**
	 * Generates a WebP file path based on the original file path.
	 *
	 * @param string $original_file_path The original file path.
	 * @return string|WP_Error The WebP file path or a WP_Error on failure.
	 */
	private function generate_webp_file_path( $original_file_path ) {
		$path_info = pathinfo( $original_file_path );

		if ( ! isset( $path_info['dirname'], $path_info['filename'] ) ) {
			return new \WP_Error(
				'nfd_performance_error',
				__( 'Invalid file path for generating WebP.', 'wp-module-performance' )
			);
		}

		return $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';
	}

	/**
	 * Saves the content to a file.
	 *
	 * @param string $file_path The path where the file will be saved.
	 * @param string $content The content to save.
	 * @return bool True on success, false on failure.
	 */
	private function save_file( $file_path, $content ) {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if ( ! $wp_filesystem->put_contents( $file_path, $content, FS_CHMOD_FILE ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the response message from wp_remote_post response.
	 *
	 * @param array $response The HTTP response from wp_remote_post.
	 * @return string|null The response message or null if unavailable.
	 */
	private function get_response_message( $response ) {
		$code    = wp_remote_retrieve_response_code( $response );
		$message = wp_remote_retrieve_response_message( $response );

		if ( $code && $message ) {
			return sprintf(
				/* translators: 1: HTTP response code, 2: Response message */
				__( 'HTTP %1$d: %2$s', 'wp-module-performance' ),
				$code,
				$message
			);
		}

		return null;
	}

	/**
	 * Replaces the original file with the optimized WebP file in the Media Library.
	 *
	 * @param int|string $media_id_or_path Media ID or original file path.
	 * @param string     $webp_file_path   The path to the optimized WebP file.
	 * @return array|WP_Error The updated upload array or WP_Error on failure.
	 */
	public function replace_original_with_webp( $media_id_or_path, $webp_file_path ) {
		$original_file_path = '';
		$upload_dir         = wp_upload_dir();
		$webp_file_url      = trailingslashit( $upload_dir['url'] ) . wp_basename( $webp_file_path );

		// Ensure the WebP file exists
		if ( ! file_exists( $webp_file_path ) || filesize( $webp_file_path ) === 0 ) {
			return new \WP_Error(
				'nfd_performance_error',
				__( 'WebP file is missing or empty.', 'wp-module-performance' )
			);
		}

		// Determine if $media_id_or_path is a Media ID or file path
		if ( is_numeric( $media_id_or_path ) && (int) $media_id_or_path > 0 ) {
			// Media ID provided
			$original_file_path = get_attached_file( $media_id_or_path );
			if ( ! $original_file_path ) {
				return new \WP_Error(
					'nfd_performance_error',
					__( 'Invalid Media ID provided.', 'wp-module-performance' )
				);
			}
		} elseif ( is_string( $media_id_or_path ) && file_exists( $media_id_or_path ) ) {
			// File path provided
			$original_file_path = $media_id_or_path;

			// Store metadata in a transient for later use
			$transient_key = 'nfd_webp_metadata_' . md5( $webp_file_path );
			set_transient(
				$transient_key,
				array(
					'webp_file_path'     => $webp_file_path,
					'original_file_path' => $original_file_path,
				),
				HOUR_IN_SECONDS
			);
		} else {
			return new \WP_Error(
				'nfd_performance_error',
				__( 'Invalid Media ID or file path provided.', 'wp-module-performance' )
			);
		}

		// Delete the original file from disk
		if ( ! $this->delete_original_file( $original_file_path ) ) {
			return new \WP_Error(
				'nfd_performance_error',
				__( 'Failed to delete the original file.', 'wp-module-performance' )
			);
		}

		// If Media ID is available, update its metadata
		if ( is_numeric( $media_id_or_path ) && $media_id_or_path > 0 ) {
			// Update the file path in the Media Library
			update_attached_file( $media_id_or_path, $webp_file_path );

			// Regenerate and update attachment metadata
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$metadata = wp_generate_attachment_metadata( $media_id_or_path, $webp_file_path );

			if ( is_wp_error( $metadata ) || empty( $metadata ) ) {
				return new \WP_Error(
					'nfd_performance_error',
					__( 'Failed to generate attachment metadata.', 'wp-module-performance' )
				);
			}

			wp_update_attachment_metadata( $media_id_or_path, $metadata );

			// Update the MIME type to reflect WebP
			$post_data = array(
				'ID'             => $media_id_or_path,
				'post_mime_type' => 'image/webp',
			);
			wp_update_post( $post_data );

			// Save metadata for optimized image
			update_post_meta( $media_id_or_path, '_nfd_performance_image_optimized', 1 );
		}

		// Return the updated upload array
		return array(
			'file' => $webp_file_path,
			'url'  => $webp_file_url,
			'type' => 'image/webp',
		);
	}

	/**
	 * Registers the WebP file as a standalone media item in the Media Library.
	 *
	 * @param string $webp_file_path The path to the optimized WebP file.
	 * @return int|WP_Error The attachment ID of the new media item, or WP_Error on failure.
	 */
	public function register_webp_as_new_media( $webp_file_path ) {
		// Prepare the attachment data
		$attachment_data = array(
			'post_mime_type' => 'image/webp',
			'post_title'     => wp_basename( $webp_file_path ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Insert the WebP file as a new attachment
		$attachment_id = wp_insert_attachment( $attachment_data, $webp_file_path );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Generate and update attachment metadata
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$metadata = wp_generate_attachment_metadata( $attachment_id, $webp_file_path );
		wp_update_attachment_metadata( $attachment_id, $metadata );

		// Save metadata for optimized image
		update_post_meta( $attachment_id, '_nfd_performance_image_optimized', 1 );

		return $attachment_id;
	}

	/**
	 * Deletes the original uploaded file from the filesystem.
	 *
	 * @param string $file_path The path to the original file.
	 * @return bool True on success, false on failure.
	 */
	public function delete_original_file( $file_path ) {
		if ( file_exists( $file_path ) ) {
			return wp_delete_file( $file_path );
		}

		return false;
	}

	/**
	 * Retrieves the monthly usage limit for image optimization from the Cloudflare Worker.
	 *
	 * @return array|WP_Error The monthly request count and limit, or a WP_Error on failure.
	 */
	public function get_monthly_usage_limit() {
		$site_url = get_site_url();
		if ( ! $site_url ) {
			return new \WP_Error(
				'nfd_performance_error',
				__( 'Error retrieving site URL.', 'wp-module-performance' )
			);
		}

		// Make a GET request to the CF Worker to retrieve monthly usage
		$response = wp_remote_get(
			self::WORKER_URL . '/?monthly-count=true',
			array(
				'timeout' => 15,
				'headers' => array(
					'X-Site-Url' => $site_url,
				),
			)
		);

		// Handle HTTP errors
		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'nfd_performance_error',
				sprintf(
					/* translators: %s: Error message */
					__( 'Error connecting to Cloudflare Worker: %s', 'wp-module-performance' ),
					$response->get_error_message()
				)
			);
		}

		// Parse response data
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return new \WP_Error(
				'nfd_performance_error',
				sprintf(
					/* translators: %s: HTTP response code */
					__( 'Unexpected response from Cloudflare Worker: HTTP %s', 'wp-module-performance' ),
					$response_code
				)
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || ! isset( $body['monthlyRequestCount'], $body['maxRequestsPerMonth'] ) ) {
			return new \WP_Error(
				'nfd_performance_error',
				__( 'Invalid response from Cloudflare Worker.', 'wp-module-performance' )
			);
		}

		$settings                  = ImageSettings::get( false );
		$settings['monthly_usage'] = $body;
		ImageSettings::update( $settings );

		return $body;
	}
}
