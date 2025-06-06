<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

/**
 * Health check for browser caching.
 */
class BrowserCachingHealthCheck extends HealthCheck {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'newfold-browser-caching';
		$this->title        = esc_html__( 'Browser Caching', 'wp-module-performance' );
		$this->passing_text = esc_html__( 'Browser caching is enabled', 'wp-module-performance' );
		$this->failing_text = esc_html__( 'Browser caching is disabled', 'wp-module-performance' );
		$this->description  = esc_html__( 'Enabling browser caching can improve performance by storing static assets in the browser for faster page loads.', 'wp-module-performance' );
	}

	/**
	 * Test if browser caching is enabled.
	 *
	 * @return bool
	 */
	public function test() {
		return get_option( 'newfold_cache_level' ) >= 1;
	}
}
