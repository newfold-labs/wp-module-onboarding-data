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
	 * Key <flow> contains a Key 'default' and a list of Key <subtype>'s.
	 * Key <flow> => 'default' contains a list of default plugin installs for <flow>.
	 * Key <flow> => <subtype> contains a list of plugins to be installed for a particular <subtype>.
	 *
	 * The final queue of Plugins to be installed makes use of a max heap and hence the greater the number the earlier
	 * a Plugin will be placed for install in the queue. This will also allow us to
	 * prevent entering negative numbers when queueing a plugin for earlier installs.
	 *
	 * @var array
	 */
	protected static $init_list = array(
		'default'   => array(
			array(
				'slug'     => 'nfd_slug_endurance_page_cache',
				'activate' => true,
				'priority' => 240,
			),
			array(
				'slug'     => 'jetpack',
				'activate' => true,
				'priority' => 220,
			),
			array(
				'slug'     => 'wordpress-seo',
				'activate' => true,
				'priority' => 200,
			),
			array(
				'slug'     => 'wpforms-lite',
				'activate' => true,
				'priority' => 180,
			),
			array(
				'slug'     => 'google-analytics-for-wordpress',
				'activate' => true,
				'priority' => 160,
			),
			array(
				'slug'     => 'optinmonster',
				'activate' => true,
				'priority' => 140,
			),
		),
		'ecommerce' => array(
			'default'        => array(
				array(
					'slug'     => 'woocommerce',
					'activate' => true,
					'priority' => 260,
				),
			),
			'bluehost'       => array(
				'default'            => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 259,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 258,
					),
				),
				'hiive_capabilities' => array(
					'hasEcomdash'     => array(
						array(
							'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
							'activate' => true,
							'priority' => 20,
						),
					),
					'hasYithExtended' => array(
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_booking',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'yith-woocommerce-ajax-search',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
							'activate' => false,
							'priority' => 266,
						),
					),
				),
			),
			'bluehost-india' => array(
				'default'            => array(
					array(
						'slug'     => 'nfd_slug_woo_razorpay',
						'activate' => true,
						'priority' => 258,
					),
				),
				'hiive_capabilities' => array(
					'hasEcomdash'     => array(
						array(
							'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
							'activate' => true,
							'priority' => 20,
						),
					),
					'hasYithExtended' => array(
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_booking',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'yith-woocommerce-ajax-search',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
							'activate' => false,
							'priority' => 266,
						),
					),
				),
			),
			'crazy-domains'  => array(
				'default'            => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 259,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 258,
					),
				),
				'hiive_capabilities' => array(
					'hasEcomdash'     => array(
						array(
							'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
							'activate' => true,
							'priority' => 20,
						),
					),
					'hasYithExtended' => array(
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_booking',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'yith-woocommerce-ajax-search',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
							'activate' => false,
							'priority' => 266,
						),
					),
				),
			),
			'hostgator-us'   => array(
				'default'            => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 259,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 258,
					),
				),
				'hiive_capabilities' => array(
					'hasEcomdash'     => array(
						array(
							'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
							'activate' => true,
							'priority' => 20,
						),
					),
					'hasYithExtended' => array(
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_booking',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'yith-woocommerce-ajax-search',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
							'activate' => false,
							'priority' => 266,
						),
					),
				),
			),
			'hostgator-br'   => array(
				'default'            => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 259,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 258,
					),
				),
				'hiive_capabilities' => array(
					'hasEcomdash'     => array(
						array(
							'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
							'activate' => true,
							'priority' => 20,
						),
					),
					'hasYithExtended' => array(
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_booking',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'yith-woocommerce-ajax-search',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
							'activate' => false,
							'priority' => 266,
						),
					),
				),
			),
		),
		'sitegen'   => array(
			'default'        => array(
				array(
					'slug'     => 'woocommerce',
					'activate' => true,
					'priority' => 260,
				),
			),
			'bluehost'       => array(
				'default'            => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 259,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 258,
					),
				),
				'hiive_capabilities' => array(
					'hasEcomdash'     => array(
						array(
							'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
							'activate' => true,
							'priority' => 20,
						),
					),
					'hasYithExtended' => array(
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_booking',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'yith-woocommerce-ajax-search',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
							'activate' => false,
							'priority' => 266,
						),
					),
				),
			),
			'bluehost-india' => array(
				'default'            => array(
					array(
						'slug'     => 'nfd_slug_woo_razorpay',
						'activate' => true,
						'priority' => 258,
					),
				),
				'hiive_capabilities' => array(
					'hasEcomdash'     => array(
						array(
							'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
							'activate' => true,
							'priority' => 20,
						),
					),
					'hasYithExtended' => array(
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_booking',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'yith-woocommerce-ajax-search',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
							'activate' => false,
							'priority' => 266,
						),
					),
				),
			),
			'crazy-domains'  => array(
				'default'            => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 259,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 258,
					),
				),
				'hiive_capabilities' => array(
					'hasEcomdash'     => array(
						array(
							'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
							'activate' => true,
							'priority' => 20,
						),
					),
					'hasYithExtended' => array(
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_booking',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'yith-woocommerce-ajax-search',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
							'activate' => false,
							'priority' => 266,
						),
					),
				),
			),
			'hostgator-us'   => array(
				'default'            => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 259,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 258,
					),
				),
				'hiive_capabilities' => array(
					'hasEcomdash'     => array(
						array(
							'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
							'activate' => true,
							'priority' => 20,
						),
					),
					'hasYithExtended' => array(
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_booking',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'yith-woocommerce-ajax-search',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
							'activate' => false,
							'priority' => 266,
						),
					),
				),
			),
			'hostgator-br'   => array(
				'default'            => array(
					array(
						'slug'     => 'nfd_slug_yith_shippo_shippings_for_woocommerce',
						'activate' => true,
						'priority' => 259,
					),
					array(
						'slug'     => 'nfd_slug_yith_paypal_payments_for_woocommerce',
						'activate' => true,
						'priority' => 258,
					),
				),
				'hiive_capabilities' => array(
					'hasEcomdash'     => array(
						array(
							'slug'     => 'nfd_slug_ecomdash_wordpress_plugin',
							'activate' => true,
							'priority' => 20,
						),
					),
					'hasYithExtended' => array(
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_booking',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'yith-woocommerce-ajax-search',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_gift_cards',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_wishlist',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_customize_myaccount_page',
							'activate' => false,
							'priority' => 260,
						),
						array(
							'slug'     => 'nfd_slug_yith_woocommerce_ajax_product_filter',
							'activate' => false,
							'priority' => 266,
						),
					),
				),
			),
		),
	);

	/**
	 * Get the list of initial plugins to be installed for a particular hosting plan.
	 *
	 * @return array
	 */
	public static function get_init() {
		$plan_data = Data::current_plan();
		$plan_flow = $plan_data['flow'];

		// The Default plugins for all types
		$init_list = self::$init_list['default'];
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
			// The Capabilities based plugins for a certain flow and brand
			if ( isset( self::$init_list[ $plan_flow ][ $current_brand ]['hiive_capabilities'] ) ) {
				$plugins_data_for_hiive_capabilities = self::$init_list[ $plan_flow ][ $current_brand ]['hiive_capabilities'];

				foreach ( $plugins_data_for_hiive_capabilities as $hiive_capability => $plugins_data ) {
					// Check if the capability is enabled on Hiive
					if ( true === Config::get_site_capability( $hiive_capability ) ) {
						// Check if there are plugins for the flag.
						if ( is_array( $plugins_data ) && ! empty( $plugins_data ) ) {
							$init_list = array_merge( $init_list, $plugins_data );
						}
					}
				}
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
