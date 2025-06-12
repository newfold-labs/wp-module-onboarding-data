<?php
namespace NewfoldLabs\WP\Module\Onboarding\Data;

use NewfoldLabs\WP\Module\Data\SiteCapabilities;

/**
 * WordPress and Onboarding configuration data.
 */
final class Config {
	/**
	 * The values need to be a string, this can later be converted to raw values.
	 *
	 * @var array
	 */
	protected static $wp_config_initialization_constants = array(
		'AUTOSAVE_INTERVAL'    => '300',
		'WP_POST_REVISIONS'    => '20',
		'EMPTY_TRASH_DAYS'     => '7',
		'WP_AUTO_UPDATE_CORE'  => 'true',
		'WP_CRON_LOCK_TIMEOUT' => '120',
	);

	/**
	 * Get the initial values for wp-config constants.
	 *
	 * @return array
	 */
	public static function get_wp_config_initialization_constants() {
		return self::$wp_config_initialization_constants;
	}

	/**
	 * Get a specific site capability.
	 *
	 * @param string $capability Name/slug of the capability.
	 * @return boolean
	 */
	public static function get_site_capability( $capability ) {
		if ( ! self::check_permissions() ) {
			return false;
		}

		$site_capabilities = new SiteCapabilities();
		return $site_capabilities->get( $capability );
	}

	/**
	 * Gets the current customer capability if he has access to AI Sitegen.
	 *
	 * @return boolean
	 */
	public static function has_ai_sitegen() {
		return self::get_site_capability( 'hasAISiteGen' );
	}

	/**
	 * Gets the current site's capability if it can import via instaWp.
	 *
	 * @return boolean
	 */
	public static function can_migrate_site() {
		return self::get_site_capability( 'canMigrateSite' );
	}

	/**
	 * Gets the current site's capability if it has solution.
	 *
	 * @return boolean
	 */
	public static function has_solution() {
		return self::get_site_capability( 'hasSolution' );
	}

	/**
	 * Checks if the request is valid and has the necessary permissions.
	 *
	 * @return bool
	 */
	private static function check_permissions(): bool {
		// Check if user is logged in and has admin capabilities
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Check if the request is a valid REST request
		$is_rest_request = false;
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// Verify the request is coming from wp-admin
			if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
				$referer = $_SERVER['HTTP_REFERER'];
				$admin_url = admin_url();
				if ( strpos( $referer, $admin_url ) === 0 ) {
					$is_rest_request = true;
				}
			}
		}

		// Check if the request is an admin page request
		$is_admin_request = is_admin();

		return $is_rest_request || $is_admin_request;
	}
}
