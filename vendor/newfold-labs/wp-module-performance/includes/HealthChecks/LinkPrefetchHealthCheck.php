<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

use NewfoldLabs\WP\Module\Performance\LinkPrefetch\LinkPrefetch;

/**
 * Health check for link prefetching.
 */
class LinkPrefetchHealthCheck extends HealthCheck {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'newfold-link-prefetch';
		$this->title        = esc_html__( 'Link Prefetching', 'wp-module-performance' );
		$this->passing_text = esc_html__( 'Link prefetching is enabled', 'wp-module-performance' );
		$this->failing_text = esc_html__( 'Link prefetching is disabled', 'wp-module-performance' );
		$this->description  = esc_html__( 'Link prefetching can improve performance by loading pages immediately before they are requested.', 'wp-module-performance' );
	}

	/**
	 * Test if link prefetching is enabled.
	 *
	 * @return bool
	 */
	public function test() {
		$enabled = get_option( LinkPrefetch::$option_name, array() );
		return isset( $enabled['activeOnDesktop'] ) && $enabled['activeOnDesktop'];
	}
}
