<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

/**
 * Health check for post revisions.
 */
class PostRevisionsHealthCheck extends HealthCheck {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'newfold-post-revisions';
		$this->title        = esc_html__( 'Post Revisions', 'wp-module-performance' );
		$this->passing_text = esc_html__( 'Number of post revisions is limited to 5 or less', 'wp-module-performance' );
		$this->failing_text = esc_html__( 'Number of post revisions is set to a high number', 'wp-module-performance' );
		$this->description  = esc_html__( 'Setting the number of post revisions to a lower number can reduce database bloat.', 'wp-module-performance' );
	}

	/**
	 * Test the number of post revisions.
	 *
	 * @return bool
	 */
	public function test() {
		return defined( 'WP_POST_REVISIONS' ) && constant( 'WP_POST_REVISIONS' ) <= 5;
	}
}
