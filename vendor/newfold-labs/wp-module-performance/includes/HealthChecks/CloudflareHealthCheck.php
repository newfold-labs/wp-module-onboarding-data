<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

/**
 * Health check for Cloudflare.
 */
class CloudflareHealthCheck extends HealthCheck {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'newfold-cloudflare';
		$this->title        = esc_html__( 'Cloudflare enabled', 'wp-module-performance' );
		$this->passing_text = esc_html__( 'Cloudflare integration is enabled', 'wp-module-performance' );
		$this->failing_text = esc_html__( 'Cloudflare integration is disabled', 'wp-module-performance' );
		$this->description  = esc_html__( 'Cloudflare integration can improve performance and security.', 'wp-module-performance' );
	}

	/**
	 * Test for Cloudflare integration.
	 *
	 * @return bool
	 */
	public function test() {
		return isset( $_SERVER['HTTP_CF_RAY'] );
	}
}
