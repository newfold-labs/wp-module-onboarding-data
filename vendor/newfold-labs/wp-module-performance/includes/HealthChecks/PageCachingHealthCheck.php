<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

/**
 * Health check for page caching.
 */
class PageCachingHealthCheck extends HealthCheck {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'newfold-page-caching';
		$this->title        = esc_html__( 'Page Caching', 'wp-module-performance' );
		$this->passing_text = esc_html__( 'Page caching is enabled', 'wp-module-performance' );
		$this->failing_text = esc_html__( 'Page caching is disabled', 'wp-module-performance' );
		$this->description  = esc_html__( 'Page caching can improve performance by bypassing PHP and database queries for faster page loads.', 'wp-module-performance' );
	}

	/**
	 * Test if page caching is enabled.
	 *
	 * @return bool
	 */
	public function test() {
		return get_option( 'newfold_cache_level' ) >= 2;
	}
}
