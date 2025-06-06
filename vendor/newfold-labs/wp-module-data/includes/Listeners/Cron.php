<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

use NewfoldLabs\WP\Module\Data\EventManager;
use NewfoldLabs\WP\Module\Data\Helpers\Plugin;

/**
 * Schedules Cron event listeners
 */
class Cron extends Listener {

	/**
	 * Register all required hooks for the listener category
	 *
	 * @see Listener::register_hooks()
	 * @see EventManager::initialize_listeners()
	 */
	public function register_hooks(): void {

		// Ensure there is a weekly option in the cron schedules
		// phpcs:ignore WordPress.WP.CronInterval.ChangeDetected
		add_filter( 'cron_schedules', array( $this, 'add_weekly_schedule' ) );

		// Weekly cron hook
		add_action( 'nfd_data_cron', array( $this, 'update' ) );

		// Register the cron task
		if ( ! wp_next_scheduled( 'nfd_data_cron' ) ) {
			wp_schedule_event(
				time() + constant( 'DAY_IN_SECONDS' ),
				'weekly',
				'nfd_data_cron'
			);
		}
	}

	/**
	 * Cron event
	 *
	 * @hooked nfd_data_cron
	 * @see Cron::register_hooks()
	 */
	public function update(): void {
		$data = array(
			'plugins' => ( new Plugin() )->collect_installed(),
		);

		$data = apply_filters( 'newfold_wp_data_module_cron_data_filter', $data );

		$this->push( 'cron', $data );
	}

	/**
	 * Add the weekly option to cron schedules if it doesn't exist
	 *
	 * @hooked cron_schedules
	 * @see wp_get_schedules()
	 *
	 * @param array<string, array{interval:int, display:string}> $schedules List of cron schedule options
	 * @return array<string, array{interval:int, display:string}>
	 */
	public function add_weekly_schedule( $schedules ): array {
		if ( ! array_key_exists( 'weekly', $schedules ) || constant( 'WEEK_IN_SECONDS' ) !== $schedules['weekly']['interval'] ) {
			$schedules['weekly'] = array(
				'interval' => constant( 'WEEK_IN_SECONDS' ),
				'display'  => __( 'Once Weekly' ),
			);
		}
		return $schedules;
	}
}
