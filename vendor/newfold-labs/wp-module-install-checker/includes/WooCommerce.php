<?php

namespace NewfoldLabs\WP\Module\InstallChecker;

class WooCommerce {

	/**
	 * Check if WooCommerce is installed and active.
	 *
	 * @return bool
	 */
	public static function isWooCommerce() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Get all WooCommerce page IDs.
	 *
	 * @return int[]
	 */
	public static function getAllPageIds() {
		return [
			wc_get_page_id( 'shop' ),
			wc_get_page_id( 'cart' ),
			wc_get_page_id( 'checkout' ),
			wc_get_page_id( 'myaccount' ),
			wc_get_page_id( 'refund_returns' ),
		];
	}

}