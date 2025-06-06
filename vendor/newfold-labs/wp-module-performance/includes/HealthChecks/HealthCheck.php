<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

/**
 * Abstract class for a health check.
 */
abstract class HealthCheck {
	/**
	 * Health check ID.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Health check title.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Health check passing text.
	 *
	 * @var string
	 */
	public $passing_text;

	/**
	 * Health check failing text.
	 *
	 * @var string
	 */
	public $failing_text;

	/**
	 * Health check description.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Health check description actions.
	 *
	 * @var string
	 */
	public $actions;

	/**
	 * Health check badge color.
	 *
	 * @var string
	 */
	public $badge_color = 'blue';

	/**
	 * Health check test.
	 *
	 * @var bool Test result.
	 */
	public function test() {
		return true;
	}

	/**
	 * Register the health check with the manager.
	 *
	 * @param array $tests Site Health tests.
	 *
	 * @return array Site Health tests.
	 */
	public function register_health_check( $tests ) {
		/**
		 * Filter to easily enable/disable the health check.
		 *
		 * @param bool $do_check Whether to run the health check.
		 */
		$do_check = apply_filters( 'newfold/features/filter/isEnabled:healthChecks:' . $this->id, true ); // phpcs:ignore WordPress.NamingConventions.ValidHookName
		if ( ! $do_check ) {
			return $tests;
		}

		// Right now, we're only supporting direct health checks.
		$tests['direct'][ $this->id ] = array(
			'label' => $this->title,
			'test'  => function () {
				$passed = $this->test();

				return array(
					'label'       => $passed ? $this->passing_text : $this->failing_text,
					'status'      => $passed ? 'good' : 'recommended',
					'description' => sprintf( '<p>%s</p>', $this->description ),
					'actions'     => $this->actions,
					'test'        => $this->id,
					'badge'       => array(
						// No text domain, as we want to match the WP core badge text.
						'label' => __( 'Performance' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain
						'color' => $this->badge_color,
					),
				);
			},
		);

		return $tests;
	}
}
