<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

/**
 * Health check for WP Cron lock timeout.
 */
class CronLockTimeoutHealthCheck extends HealthCheck {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'wp-cron-lock-timeout';
		$this->title        = esc_html__( 'WP Cron Lock Timeout', 'wp-module-performance' );
		$this->passing_text = esc_html__( 'Cron lock timeout is set to 60 seconds or less.', 'wp-module-performance' );
		$this->failing_text = esc_html__( 'Cron lock timeout is set to a high number.', 'wp-module-performance' );
		$this->description  = esc_html__( 'Cron lock timeout affects how long a cron job can run for. Setting it to a lower number can improve performance.', 'wp-module-performance' );
	}

	/**
	 * Test the WP Cron lock timeout setting.
	 *
	 * @return bool
	 */
	public function test() {
		return defined( 'WP_CRON_LOCK_TIMEOUT' ) && constant( 'WP_CRON_LOCK_TIMEOUT' ) <= 300;
	}
}
