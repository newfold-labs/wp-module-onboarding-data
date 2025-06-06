<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

/**
 * Health check for CSS concatenation.
 */
class ConcatenateCSSHealthCheck extends HealthCheck {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'newfold-concatenate-css';
		$this->title        = esc_html__( 'Concatenate CSS', 'wp-module-performance' );
		$this->passing_text = esc_html__( 'CSS files are concatenated', 'wp-module-performance' );
		$this->failing_text = esc_html__( 'CSS files are not concatenated', 'wp-module-performance' );
		$this->description  = esc_html__( 'Concatenating CSS can improve performance by reducing the number of requests.', 'wp-module-performance' );
	}

	/**
	 * Test if CSS files are concatenated.
	 *
	 * @return bool
	 */
	public function test() {
		return ! empty( get_option( 'jetpack_boost_status_minify-css', array() ) );
	}
}
