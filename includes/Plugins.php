<?php
namespace NewfoldLabs\WP\Module\Onboarding\Data;

use NewfoldLabs\WP\Module\Installer\Data\Plugins as PluginsInstaller;

use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * List of Plugin Slugs/URLs/Domains
 */
final class Plugins {
	/**
	 * Initial plugins to be installed classified based on the hosting plan.
	 * Key 'default' contains a list of default plugins to be installed irrespective of the plan.
	 * Key 'site-capabilities' contains a list of plugins to be installed for a particular site capability.
	 * Key <flow> contains a Key 'default' and a list of Key <subtype>'s.
	 * Key <flow> => 'default' contains a list of default plugin installs for <flow>.
	 * The final queue of Plugins to be installed makes use of a max heap and hence the greater the number the earlier
	 * a Plugin will be placed for install in the queue. This will also allow us to
	 * prevent entering negative numbers when queueing a plugin for earlier installs.
	 *
	 * @var array
	 */
	protected static $init_list = array(
		'default'           => array(
			array(
				'slug'     => 'nfd_slug_endurance_page_cache',
				'activate' => true,
				'priority' => 240,
			),
			array(
				'slug'     => 'jetpack',
				'activate' => true,
				'priority' => 250,
			),
			array(
				'slug'     => 'wordpress-seo',
				'activate' => true,
				'priority' => 260,
			),
			array(
				'slug'     => 'wpforms-lite',
				'activate' => true,
				'priority' => 270,
			),
			array(
				'slug'     => 'google-analytics-for-wordpress',
				'activate' => true,
				'priority' => 280,
			),
			array(
				'slug'     => 'optinmonster',
				'activate' => true,
				'priority' => 290,
			),
		),
		'site-capabilities' => array(
			'hasEcomdash'     => array(
				array(
					'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
					'activate' => true,
					'priority' => 220,
				),
			),
			'hasYithExtended' => array(
				array(
					'slug'     => 'woocommerce',
					'activate' => true,
					'priority' => 300,
				),
				array(
					'slug'     => 'nfd_slug_yith_woocommerce_booking',
					'activate' => true,
					'priority' => 100,
				),
				array(
					'slug'     => 'yith-woocommerce-ajax-search',
					'activate' => true,
					'priority' => 120,
				),
				array(
					'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
					'activate' => true,
					'priority' => 140,
				),
				array(
					'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
					'activate' => true,
					'priority' => 160,
				),
				array(
					'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
					'activate' => true,
					'priority' => 180,
				),
				array(
					'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
					'activate' => true,
					'priority' => 200,
				),
				array(
					'slug'     => 'nfd_slug_wonder_cart',
					'activate' => true,
					'priority' => 210,
				),
			),
		),
		'ecommerce'         => array(
			'default'        => array(
				array(
					'slug'     => 'woocommerce',
					'activate' => true,
					'priority' => 300,
				),
			),
			'bluehost'       => array(
				'default' => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 60,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 80,
					),
				),
			),
			'bluehost-india' => array(
				'default' => array(
					array(
						'slug'     => 'nfd_slug_woo_razorpay',
						'activate' => true,
						'priority' => 80,
					),
				),
			),
			'crazy-domains'  => array(
				'default' => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 60,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 80,
					),
				),
			),
			'hostgator-us'   => array(
				'default' => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 60,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 80,
					),
				),
			),
			'hostgator-br'   => array(
				'default' => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 60,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 80,
					),
				),
			),
		),
		'sitegen'           => array(
			'default'        => array(
				array(
					'slug'     => 'woocommerce',
					'activate' => true,
					'priority' => 300,
				),
			),
			'bluehost'       => array(
				'default' => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 60,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 80,
					),
				),
			),
			'bluehost-india' => array(
				'default' => array(
					array(
						'slug'     => 'nfd_slug_woo_razorpay',
						'activate' => true,
						'priority' => 80,
					),
				),
			),
			'crazy-domains'  => array(
				'default' => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 60,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 80,
					),
				),
			),
			'hostgator-us'   => array(
				'default' => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 60,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 80,
					),
				),
			),
			'hostgator-br'   => array(
				'default' => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 60,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 80,
					),
				),
			),
		),
	);

	/**
	 * An array of capabilities that should not be run if the site has solution.
	 *
	 * @var array
	 */
	protected static $solution_override_capabilities = array( 'hasYithExtended' );

	/**
	 * Get the list of initial plugins to be installed for a particular hosting plan.
	 *
	 * @return array
	 */
	public static function get_init() {
		// The Default plugins for all types
		$init_list = self::$init_list['default'];

		// The default plugins for Site Capabilities.
		if ( isset( self::$init_list['site-capabilities'] ) ) {
			$plugins_data_for_site_capabilities = self::$init_list['site-capabilities'];

			foreach ( $plugins_data_for_site_capabilities as $site_capability => $plugins_data ) {
				// Skip capability installation if solution is present.
				if ( Config::has_solution() &&
						in_array( $site_capability, self::$solution_override_capabilities, true ) ) {
					continue;
				}
				// Check if the capability is enabled on Hiive
				if ( true === Config::get_site_capability( $site_capability ) ) {
					// Check if there are plugins for the flag.
					if ( is_array( $plugins_data ) && ! empty( $plugins_data ) ) {
						$init_list = array_merge( $init_list, $plugins_data );
					}
				}
			}
		}

		$plan_data = Data::current_plan();
		$plan_flow = $plan_data['flow'];
		if ( $plan_flow && isset( self::$init_list[ $plan_flow ] ) ) {
			// The Default plugins for a specific flow
			if ( isset( self::$init_list[ $plan_flow ]['default'] ) ) {
				$init_list = array_merge( $init_list, self::$init_list[ $plan_flow ]['default'] );
			}

			$current_brand = Data::current_brand()['brand'];
			// The Default plugins for a certain flow and brand
			if ( isset( self::$init_list[ $plan_flow ][ $current_brand ]['default'] ) ) {
				$init_list = array_merge( $init_list, self::$init_list[ $plan_flow ][ $current_brand ]['default'] );
			}
		}

		return $init_list;
	}

	/**
	 * Prevent redirect to woo wizard after activation of woocommerce.
	 *
	 * @return void
	 */
	public static function wc_prevent_redirect_on_activation() {
		\delete_transient( '_wc_activation_redirect' );
	}

	/**
	 * List of plugins that should stay active even with the filter option
	 *
	 * @return array
	 */
	public static function get_active_plugins_list() {
		return array(
			container()->plugin()->basename,
			isset( PluginsInstaller::get_wp_slugs()['woocommerce']['path'] ) ? PluginsInstaller::get_wp_slugs()['woocommerce']['path'] : false,
		);
	}
}
