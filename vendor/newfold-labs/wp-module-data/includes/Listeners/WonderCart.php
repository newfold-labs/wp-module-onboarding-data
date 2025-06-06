<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors WonderCart events
 */
class WonderCart extends Listener {

	/**
	 * Register the hooks for the listener
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'rest_after_insert_yith_campaign', array( $this, 'register_campaign' ), 10 );
		add_action( 'yith_sales_edit_campaign_event_modal_opened', array( $this, 'create_campaign_modal_open' ), 10, 2 );
		add_action( 'yith_sales_edit_campaign_event_campaign_selected', array( $this, 'campaign_selected' ), 10, 2 );
		add_action( 'yith_sales_edit_campaign_event_campaign_abandoned', array( $this, 'campaign_abandoned' ), 10, 2 );
		add_action( 'woocommerce_payment_complete', array( $this, 'checkout_campaigns_used' ) );
	}

	/**
	 * Campaign created
	 *
	 * @param string $post Campaign data
	 *
	 * @return string The post value
	 */
	public function register_campaign( $post ) {
		$campaign = yith_sales_get_campaign( $post->ID );
		if ( $campaign ) {
			$type = $campaign->get_type();

			$data = array(
				'label_key' => 'type',
				'type'      => $type,
			);

			$this->push(
				'campaign_created',
				$data
			);
		}

		return $post;
	}

	/**
	 * Track wonder_cart create campaign modal window open
	 * Send data to hiive

	 * @param string $args A list of details that were involved on the event.
	 * @param string $event The name of the event.

	 * @return void
	 */
	public function create_campaign_modal_open( $args, $event ) {
		$url  = is_ssl() ? 'https://' : 'http://';
		$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$data = array(
			'label_key' => 'trigger',
			'trigger'   => 'Campaign Modal Open',
			'page'      => $url,
		);

		$this->push(
			'modal_open',
			$data
		);
	}

	/**
	 * Track wonder_cart campaign selection
	 * Send data to hiive

	 * @param string $args A list of details that were involved on the event.
	 * @param string $event The name of the event.

	 * @return void
	 */
	public function campaign_selected( $args, $event ) {
		$data = array(
			'label_key'     => 'campaign_slug',
			'type'          => $args['type'],
			'campaign_slug' => $args['type'],
		);

		$this->push(
			'campaign_selected',
			$data
		);
	}

	/**
	 * Track wonder_cart campaign abondoned
	 * Send data to hiive

	 * @param string $args A list of details that were involved on the event.
	 * @param string $event The name of the event.

	 * @return void
	 */
	public function campaign_abandoned( $args, $event ) {
		$data = array(
			'label_key'     => 'campaign_slug',
			'type'          => $args['type'],
			'campaign_slug' => $args['type'] . '-' . $args['id'],
		);

		$this->push(
			'campaign_abondoned',
			$data
		);
	}

	/**
	 * Track wonder_cart campaigns used in checkout page
	 * Send data to hiive

	 * @return void
	 */
	public function checkout_campaigns_used() {
		$campaigns      = array();
		$campaign_total = 0;

		$cart = WC()->cart;

		// To track Cart Discount
		foreach ( $cart->get_applied_coupons() as $coupon_item ) {
			array_push( $campaigns, $coupon_item );
			$campaign_total += $cart->coupon_discount_totals[ $coupon_item ];
		}

		// To track free shipping campaign ( Using reflection to access protected properties)
		$reflection_class          = new \ReflectionClass( $cart );
		$shipping_methods_property = $reflection_class->getProperty( 'shipping_methods' );
		$shipping_methods_property->setAccessible( true );
		$shipping_methods = $shipping_methods_property->getValue( $cart );
		foreach ( $shipping_methods as $shipping_method ) {
			if ( 'yith_sales_free_shipping' === $shipping_method->id ) {
				array_push( $campaigns, 'yith_sales_free_shipping' );
			}
		}

		// To track rest of the campaigns
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['yith_sales'] ) && isset( $cart_item['yith_sales']['campaigns'] ) ) {
				$campaign_type = $cart_item['yith_sales_discounts']['type'];
				array_push( $campaigns, $campaign_type );
				$campaign_total += $cart_item['yith_sales_discounts']['price_base'] - $cart_item['yith_sales_discounts']['price_adjusted'];
			}
		}
		if ( count( $campaigns ) > 0 ) {
			$data = array(
				'label_key'      => 'type',
				'type'           => array_unique( $campaigns ),
				'campaign_count' => count( $campaigns ),
				'campaign_total' => '$' . $campaign_total,
			);
			$this->push(
				'checkout_campaign_type',
				$data
			);
		}
	}
}
