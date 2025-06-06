<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

/**
 * Health check for JavaScript concatenation.
 */
class ConcatenateJsHealthCheck extends HealthCheck {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'newfold-concatenate-js';
		$this->title        = esc_html__( 'Concatenate JavaScript', 'wp-module-performance' );
		$this->passing_text = esc_html__( 'JavaScript files are concatenated', 'wp-module-performance' );
		$this->failing_text = esc_html__( 'JavaScript files are not concatenated', 'wp-module-performance' );
		$this->description  = esc_html__( 'Concatenating JavaScript can improve performance by reducing the number of requests.', 'wp-module-performance' );
	}

	/**
	 * Test if JavaScript files are concatenated.
	 *
	 * @return bool
	 */
	public function test() {
		return ! empty( get_option( 'jetpack_boost_status_minify-js', array() ) );
	}
}
