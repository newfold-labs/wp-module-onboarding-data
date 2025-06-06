<?php

namespace NewfoldLabs\WP\Context;

/**
 * This class adds brand helpers to the context with dot notation access.
 **/
class Brand {

	/**
	 * Helper to get the base brand name
	 */
	public function base() {
		return getContext( 'brand.name' );
	}

	/**
	 * Helper to get the sub brand name
	 */
	public function sub() {
		return getContext( 'brand.sub' );
	}

	/**
	 * Helper to get the brand region
	 */
	public function region() {
		return getContext( 'brand.region' );
	}
}
