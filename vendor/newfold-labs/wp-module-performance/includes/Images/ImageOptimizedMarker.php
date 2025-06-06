<?php

namespace NewfoldLabs\WP\Module\Performance\Images;

use NewfoldLabs\WP\Module\Performance\Services\I18nService;

/**
 * Marks optimized images in the WordPress Media Library.
 */
class ImageOptimizedMarker {

	/**
	 * Initializes the class by registering hooks.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_marker_assets' ) );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'add_media_library_data_attribute' ), 10, 2 );
	}

	/**
	 * Establish whether the current page allows scripts to be enqueued.
	 *
	 * @return boolean
	 */
	private function is_enqueue_allowed() {
		global $pagenow;

		$excluded_pages = array(
			'admin.php',
			'themes.php',
			'plugins.php',
			'users.php',
			'tools.php',
			'import.php',
			'export.php',
			'site-health.php',
			'export-personal-data.php',
			'erase-personal-data.php',
			'theme-editor.php',
			'plugin-editor.php',
		);

		$is_excluded = in_array( $pagenow, $excluded_pages, true );

		return apply_filters( 'newfold_performance_optimized_image_marker_enqueue_allowed', ! $is_excluded );
	}

	/**
	 * Enqueues JS and CSS files for marking optimized images.
	 */
	public function enqueue_marker_assets() {
		wp_register_script(
			'nfd-performance-optimizer-marker',
			NFD_PERFORMANCE_BUILD_URL . '/assets/image-optimized-marker/image-optimized-marker.min.js',
			array( 'wp-api-fetch', 'wp-element', 'wp-i18n' ),
			filemtime( NFD_PERFORMANCE_BUILD_DIR . '/assets/image-optimized-marker/image-optimized-marker.min.js' ),
			true
		);

		I18nService::load_js_translations(
			'wp-module-performance',
			'nfd-performance-optimizer-marker',
			NFD_PERFORMANCE_PLUGIN_LANGUAGES_DIR
		);

		wp_register_style(
			'nfd-performance-optimizer-marker-style',
			NFD_PERFORMANCE_BUILD_URL . '/assets/image-optimized-marker/image-optimized-marker.min.css',
			array(),
			filemtime( NFD_PERFORMANCE_BUILD_DIR . '/assets/image-optimized-marker/image-optimized-marker.min.css' )
		);

		if ( $this->is_enqueue_allowed() ) {
			wp_enqueue_style( 'nfd-performance-optimizer-marker-style' );
			wp_enqueue_script( 'nfd-performance-optimizer-marker' );
		}
	}

	/**
	 * Adds a custom data attribute to media library items if optimized.
	 *
	 * @param array   $response  The prepared attachment response.
	 * @param WP_Post $attachment The current attachment object.
	 *
	 * @return array The modified response.
	 */
	public function add_media_library_data_attribute( $response, $attachment ) {
		if ( get_post_meta( $attachment->ID, '_nfd_performance_image_optimized', true ) ) {
			$response['nfdPerformanceImageOptimized'] = true;
		}

		return $response;
	}
}
