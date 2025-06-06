<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

/**
 * Health check for lazy loading.
 */
class LazyLoadingHealthCheck extends HealthCheck {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'newfold-lazy-loading';
		$this->title        = esc_html__( 'Lazy Loading', 'wp-module-performance' );
		$this->passing_text = esc_html__( 'Lazy loading is enabled', 'wp-module-performance' );
		$this->failing_text = esc_html__( 'Lazy loading is disabled', 'wp-module-performance' );
		$this->description  = esc_html__( 'Lazy loading can improve performance by only loading images when they are in view.', 'wp-module-performance' );
	}

	/**
	 * Test if lazy loading is enabled.
	 *
	 * @return bool
	 */
	public function test() {
		$image_optimization = get_option( 'nfd_image_optimization', array() );
		return isset( $image_optimization['lazy_loading'], $image_optimization['lazy_loading']['enabled'] ) && $image_optimization['lazy_loading']['enabled'];
	}
}
