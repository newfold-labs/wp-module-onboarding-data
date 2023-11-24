<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

use NewfoldLabs\WP\Module\AI\SiteGen\SiteGen;
use NewfoldLabs\WP\Module\Onboarding\Data\Data;

/**
 * Class SiteGenService
 *
 * Class for handling SiteGen interactions.
 */
class SiteGenService {

	/**
	 * Determines whether the given identifier is valid.
	 *
	 * @param string $key The identifier to be evaluated.
	 * @return boolean
	 */
	public static function is_identifier( $key ) {
		$identifiers = array(
			'siteclassification'   => true,
			'targetaudience'       => true,
			'contenttones'         => true,
			'contentstructure'     => true,
			'colorpalette'         => true,
			'sitemap'              => true,
			'pluginrecommendation' => true,
			'fontpair'             => true,
		);

		return isset( $identifiers[ $key ] );
	}

	/**
	 * Checks if the site is eligible for SiteGen Capabilities.
	 *
	 * @return boolean
	 */
	public static function is_enabled() {
		if ( ! ( class_exists( 'NewfoldLabs\WP\Module\AI\SiteGen\SiteGen' ) ) ) {
			return false;
		}
		return isset( Data::current_brand()['config']['enabled_flows']['sitegen'] )
		&& true === Data::current_brand()['config']['enabled_flows']['sitegen'];
	}


	/**
	 * Sends the data required for SiteGen Generation
	 *
	 * @param string|Object $site_info The prompt that configures the Site gen object.
	 * @param string        $identifier The identifier for Generating Site Meta.
	 * @param boolean       $skip_cache To override the cache and fetch the data.
	 * @return array
	 */
	public static function instantiate_site_meta( $site_info, $identifier, $skip_cache = false ) {

		if ( self::is_identifier( $identifier ) ) {
			sleep( 8 );
			return 'Imitate Call';
			return SiteGen::generate_site_meta( $site_info, $identifier, $skip_cache );
		}

		// Imitates the error pattern returned by SiteGen Class
		return array(
			'error' => __( 'The given identifier is not valid' ),
		);
	}

}
