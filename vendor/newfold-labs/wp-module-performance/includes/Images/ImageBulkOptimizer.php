<?php

namespace NewfoldLabs\WP\Module\Performance\Images;

use NewfoldLabs\WP\Module\Performance\Services\I18nService;

/**
 * Manages bulk optimization functionality for the Media Library.
 */
class ImageBulkOptimizer {

	/**
	 * Constructor to initialize the bulk optimizer feature.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_bulk_optimizer_script' ) );
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

		return apply_filters( 'newfold_performance_image_bulk_optimizer_enqueue_allowed', ! $is_excluded );
	}
	/**
	 * Enqueues the bulk optimizer script.
	 */
	public function enqueue_bulk_optimizer_script() {
		wp_register_script(
			'nfd-performance-bulk-optimizer',
			NFD_PERFORMANCE_BUILD_URL . '/assets/image-bulk-optimizer/image-bulk-optimizer.min.js',
			array( 'wp-api-fetch', 'wp-element', 'wp-i18n' ),
			filemtime( NFD_PERFORMANCE_BUILD_DIR . '/assets/image-bulk-optimizer/image-bulk-optimizer.min.js' ),
			true
		);

		I18nService::load_js_translations(
			'wp-module-performance',
			'nfd-performance-bulk-optimizer',
			NFD_PERFORMANCE_PLUGIN_LANGUAGES_DIR
		);

		wp_register_style(
			'nfd-performance-bulk-optimizer-style',
			NFD_PERFORMANCE_BUILD_URL . '/assets/image-bulk-optimizer/image-bulk-optimizer.min.css',
			array(),
			filemtime( NFD_PERFORMANCE_BUILD_DIR . '/assets/image-bulk-optimizer/image-bulk-optimizer.min.css' )
		);

		if ( $this->is_enqueue_allowed() ) {
			wp_enqueue_style( 'nfd-performance-bulk-optimizer-style' );
			wp_enqueue_script( 'nfd-performance-bulk-optimizer' );
			wp_add_inline_script(
				'nfd-performance-bulk-optimizer',
				$this->get_inline_script(),
				'before'
			);
		}
	}

	/**
	 * Generates inline settings for the bulk optimizer script.
	 *
	 * @return string JavaScript code to inline.
	 */
	private function get_inline_script() {
		$api_url = add_query_arg(
			'rest_route',
			'/newfold-performance/v1/images/optimize',
			get_rest_url()
		);

		return sprintf(
			'window.nfdPerformance = window.nfdPerformance || {};
			 window.nfdPerformance.imageOptimization = window.nfdPerformance.imageOptimization || {};
			 window.nfdPerformance.imageOptimization.bulkOptimizer = {
				 apiUrl: "%s"
			 };',
			esc_url( $api_url )
		);
	}
}
