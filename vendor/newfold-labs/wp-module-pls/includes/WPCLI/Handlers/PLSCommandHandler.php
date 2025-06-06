<?php

namespace NewfoldLabs\WP\Module\PLS\WPCLI\Handlers;

use NewfoldLabs\WP\Module\PLS\Utilities\PLSUtility;
use WP_CLI;

/**
 * Class PLSCommandHandler
 *
 * Handles WP-CLI custom commands for the PLS module.
 */
class PLSCommandHandler {

	/**
	 * Provisions a new license for the given plugin.
	 *
	 * ## OPTIONS
	 *
	 * <plugin_slug>
	 * : The slug of the plugin for which to provision the license.
	 *
	 * <provider>
	 * : The provider name.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pls provision <plugin_slug> <provider>
	 *
	 * @param array $args Positional arguments, where the first element is the plugin slug and the second is the provider name.
	 * @return void
	 */
	public function provision( $args ) {
		$plugin_slug = $args[0];
		$provider    = $args[1];

		$utility = new PLSUtility();
		$result  = $utility->provision_license( $plugin_slug, $provider );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		} else {
			WP_CLI::success( 'License provisioned: ' . wp_json_encode( $result ) );
		}
	}


	/**
	 * Retrieves the current license validity status for the given plugin.
	 *
	 * ## OPTIONS
	 *
	 * <plugin_slug>
	 * : The slug of the plugin for which to check the license status.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pls status <plugin_slug>
	 *
	 * @param array $args Positional arguments, where the first element is the plugin slug.
	 * @return void
	 */
	public function status( $args ) {
		$plugin_slug = $args[0];

		$utility = new PLSUtility();
		$result  = $utility->check_license_status( $plugin_slug );

		if ( $result ) {
			WP_CLI::success( 'License Valid' );
		} else {
			WP_CLI::error( 'License Invalid' );
		}
	}

	/**
	 * Activates a license for the given plugin.
	 *
	 * ## OPTIONS
	 *
	 * <plugin_slug>
	 * : The slug of the plugin for which to activate the license.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pls activate <plugin_slug>
	 *
	 * @param array $args Positional arguments, where the first element is the plugin slug.
	 * @return void
	 */
	public function activate( $args ) {
		$plugin_slug = $args[0];

		$utility = new PLSUtility();
		$result  = $utility->activate_license( $plugin_slug );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		} else {
			WP_CLI::success( 'License activated: ' . wp_json_encode( $result ) );
		}
	}
}
