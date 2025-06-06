<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

/**
 * Health check for permalinks.
 */
class PermalinksHealthCheck extends HealthCheck {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'newfold-permalinks';
		$this->title        = esc_html__( 'Permalinks', 'wp-module-performance' );
		$this->passing_text = esc_html__( 'Permalinks are pretty', 'wp-module-performance' );
		$this->failing_text = esc_html__( 'Permalinks are not set up', 'wp-module-performance' );
		$this->description  = esc_html__( 'Setting permalinks to anything other than plain can improve performance and SEO.', 'wp-module-performance' );
	}

	/**
	 * Test the permalinks setting.
	 *
	 * @return bool
	 */
	public function test() {
		return ! empty( get_option( 'permalink_structure' ) );
	}
}
