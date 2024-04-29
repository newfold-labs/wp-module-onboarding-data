<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data;

/**
 * Class SiteGenData
 */
final class SiteGen {
	/**
	 * Contains the map of the questionnaire for sitegen prompt.
	 *
	 * @var array
	 */
	public static function get_site_details_questionnaire() {
		return array(
			'businessName'   => array(
				'question' => __( '1. Do you have a business name or website title?', 'wp-module-onboarding-data' ),
				'prompt'   => __( 'My business name is', 'wp-module-onboarding-data' ),
			),
			'websiteType'    => array(
				'question'    => __( '2. What type of website are you making?', 'wp-module-onboarding-data' ),
				'placeholder' => __( 'e.g. Graphic design portfolio', 'wp-module-onboarding-data' ),
				'prompt'      => __( 'I am making a website type of', 'wp-module-onboarding-data' ),
			),
			'uniqueBusiness' => array(
				'question'    => __( '3. Is there anything unique about your business or brand?', 'wp-module-onboarding-data' ),
				'placeholder' => __( 'e.g. Unique product, amazing customer service, customizations, etc.', 'wp-module-onboarding-data' ),
				'prompt'      => __( 'Unique about my business is', 'wp-module-onboarding-data' ),
			),
		);
	}
}
