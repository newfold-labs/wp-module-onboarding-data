<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors generic plugin events
 */
class BluehostPlugin extends Listener {

	/**
	 * Register the hooks for the subscriber
	 *
	 * @return void
	 */
	public function register_hooks() {

		// Site Launched - Coming Soon page disabled
		add_action( 'newfold/coming-soon/disabled', array( $this, 'site_launch' ) );

		// SSO (Legacy)
		add_action( 'eig_sso_success', array( $this, 'sso_success' ), 10, 2 );
		add_action( 'eig_sso_fail', array( $this, 'sso_fail' ) );

		// SSO
		add_action( 'newfold_sso_success', array( $this, 'sso_success' ), 10, 2 );
		add_action( 'newfold_sso_fail', array( $this, 'sso_fail' ) );

		// Staging
		add_action( 'bh_staging_command', array( $this, 'staging' ) );

		// Features
		add_action( 'newfold/features/action/onEnable', array( $this, 'feature_enable' ) );
		add_action( 'newfold/features/action/onDisable', array( $this, 'feature_disable' ) );
	}

	/**
	 * Disable Coming Soon
	 */
	public function site_launch() {
		$mm_install_time = get_option( 'mm_install_date', gmdate( 'M d, Y' ) );
		$install_time    = apply_filters( 'nfd_install_date_filter', strtotime( $mm_install_time ) );

		$this->push(
			'site_launched',
			array(
				'ttl' => time() - $install_time,
			)
		);
	}

	/**
	 * Successful SSO
	 *
	 * @param \WP_User $user User who logged in
	 * @param string   $redirect URL redirected to after login
	 *
	 * @return void
	 */
	public function sso_success( $user, $redirect ) {
		$data = array(
			'label_key'    => 'status',
			'status'       => 'success',
			'landing_page' => $redirect,
		);
		$this->push( 'sso', $data );
	}

	/**
	 * SSO failure
	 *
	 * @return void
	 */
	public function sso_fail() {
		$this->push(
			'sso',
			array(
				'label_key' => 'status',
				'status'    => 'fail',
			)
		);
	}

	/**
	 * Staging commands executed
	 *
	 * @param string $command The staging command executed
	 *
	 * @return void
	 */
	public function staging( $command ) {
		$this->push(
			'staging',
			array(
				'label_key' => 'command',
				'command'   => $command,
			)
		);
	}

	/**
	 * Feature Enable event
	 *
	 * @param string $name The feature name
	 *
	 * @return void
	 */
	public function feature_enable( $name ) {
		$this->push(
			'features',
			array(
				'label_key' => 'enabled',
				'feature'   => $name,
			)
		);
	}

	/**
	 * Feature Disable event
	 *
	 * @param string $name The feature name
	 *
	 * @return void
	 */
	public function feature_disable( $name ) {
		$this->push(
			'features',
			array(
				'label_key' => 'disabled',
				'feature'   => $name,
			)
		);
	}
}
