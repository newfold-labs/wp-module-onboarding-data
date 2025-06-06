<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

use NewfoldLabs\WP\Module\Data\Helpers\SiteHealth as SiteHealthHelper;

/**
 * Monitors Site Health events
 */
class SiteHealth extends Listener {

	/**
	 * Register the hooks for the subscriber
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Site Health tests have run and the transient is updated.
		add_action( 'set_transient_health-check-site-status-result', array( $this, 'tests_run' ) );
	}

	/**
	 * Report Site Health related data
	 *
	 * @since 1.7.0
	 *
	 * @param string $value A JSON string with the results of Site Health tests
	 * @return void
	 */
	public function tests_run( $value ) {
		$this->push(
			'site_health_score',
			array(
				'label_key' => 'score',
				'score'     => SiteHealthHelper::calculate_score( $value ),
			)
		);
		$this->push(
			'site_health_debug',
			array(
				'debug_data' => wp_json_encode( SiteHealthHelper::get_safe_data() ),
			)
		);
	}
}
