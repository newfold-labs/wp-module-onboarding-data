<?php
namespace NewfoldLabs\WP\Module\Onboarding\Data;

use NewfoldLabs\WP\Module\CustomerBluehost\CustomerBluehost;
use NewfoldLabs\WP\Module\Onboarding\Data\Flows\Flows;
use NewfoldLabs\WP\Module\Installer\Services\PluginInstaller;

use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * CRUD methods for Onboarding config for use in API, CLI and runtime.
 */
final class Data {
	/**
	 * Runtime data for Onboarding application
	 */
	public static function runtime() {
		return array(
			'buildUrl'            => \NFD_ONBOARDING_BUILD_URL,
			'siteUrl'             => \get_site_url(),
			'restUrl'             => \get_home_url() . '/index.php?rest_route=',
			'adminUrl'            => \admin_url(),
			'currentBrand'        => self::current_brand(),
			'currentPlan'         => self::current_plan(),
			'currentFlow'         => self::current_flow(),
			'pluginInstallHash'   => PluginInstaller::rest_get_plugin_install_hash(),
			'previewSettings'     => array(
				'settings'        => Preview::get_settings(),
				'stepPreviewData' => Themes::step_preview_data(),
			),
			'currentUserDetails'  => self::wp_current_user_details(),
			'isFreshInstallation' => self::is_fresh_installation(),
			'sentryInitDsnURL'    => 'https://cd5bd4c30b914e0d1d0f49413e600afa@o4506197201715200.ingest.us.sentry.io/4507383861805056',
		);
	}

	/**
	 * Establish the brand to apply to the Onboarding experience.
	 *
	 * @return array The configuration array of the current brand. If the specified brand is not found,
	 *               returns the default brand configuration.
	 */
	public static function current_brand() {
		$brands = Brands::get_brands();

		return array_key_exists( NFD_ONBOARDING_PLUGIN_BRAND, $brands ) ?
			$brands[ NFD_ONBOARDING_PLUGIN_BRAND ] :
			Brands::get_default_brand();
	}


	/**
	 * Get the current hosting plan information.
	 *
	 * @return array
	 */
	public static function current_plan() {
		$is_sitegen = Flows::is_sitegen();
		if ( $is_sitegen ) {
			return array(
				'flow'    => 'sitegen',
				'subtype' => null,
				'type'    => null,
			);
		}

		$customer_data = self::customer_data();

		$current_flow = Flows::get_flow_from_customer_data( $customer_data );
		if ( false !== $current_flow ) {
			return array(
				'flow'    => $current_flow,
				'subtype' => $customer_data['plan_subtype'],
				'type'    => $customer_data['plan_type'],
			);
		}

		$current_flow = Flows::get_flow_from_params();
		if ( false !== $current_flow ) {
			return array(
				'flow'    => $current_flow,
				'subtype' => 'wc_priority',
				'type'    => null,
			);
		}

		$current_flow = Flows::get_flow_from_top_priority();
		if ( 'ecommerce' === $current_flow ) {
			return array(
				'flow'    => 'ecommerce',
				'subtype' => 'wc_priority',
				'type'    => null,
			);
		} elseif ( Flows::get_default_flow() === $current_flow ) {
			return array(
				'flow'    => $current_flow,
				'subtype' => null,
				'type'    => null,
			);
		}

		$current_flow = Flows::get_flow_from_plugins();
		if ( false !== $current_flow ) {
			switch ( $current_flow ) {
				case 'ecommerce':
					return array(
						'flow'    => 'ecommerce',
						'subtype' => 'wc_priority',
						'type'    => null,
					);
			}
		}

		return array(
			'flow'    => Flows::get_default_flow(),
			'subtype' => null,
			'type'    => null,
		);
	}

	/**
	 * Get the current onboarding flow.
	 *
	 * @return string
	 */
	public static function current_flow() {
		$current_plan = self::current_plan();
		return $current_plan['flow'];
	}

	/**
	 * Get the current customer data using the Bluehost customer data module.
	 *
	 * @return array
	 */
	public static function customer_data() {
		if ( class_exists( 'NewfoldLabs\WP\Module\CustomerBluehost\CustomerBluehost' ) ) {
			return CustomerBluehost::collect();
		}
		return array();
	}

	/**
	 * Determine whether the site is in coming soon mode.
	 *
	 * @return boolean
	 */
	public static function coming_soon() {
		if ( ! container()->has( 'comingSoon' ) ) {
			return false;
		}

		$coming_soon_service = container()->get( 'comingSoon' );
		return $coming_soon_service->is_enabled();
	}

	/**
	 * Get the current WordPress admin user details.
	 *
	 * @return array
	 */
	public static function wp_current_user_details() {
		$user = wp_get_current_user();
		if ( $user->exists() ) {
			return array(
				'displayName' => $user->display_name,
				'avatarUrl'   => get_avatar_url( $user->ID ),
			);
		}

		// If no user is found, return an empty array or default values as appropriate
		return array(
			'displayName' => '',
			'avatarUrl'   => '',
		);
	}

	/**
	 * Returns whether the site is a fresh installation or not.
	 *
	 * @return boolean
	 */
	public static function is_fresh_installation() {
		if ( container()->has( 'isFreshInstallation' ) ) {
			return container()->get( 'isFreshInstallation' );
		}

		return false;
	}
}
