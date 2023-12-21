<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data;

/**
 * Class SiteGenData
 */
final class SiteGenData {
	/**
	 * Contains the map of site features for a particular flow and plan.
	 *
	 * @var array
	 */
	protected static function get_site_details_questionnaire() {
		return array(
			'businessName'  => array(
				'question'		=>__(  "1. Do you have a business name or website title?", 'wp-module-onboarding' ),
				'prompt'		=>__(  "My business name is", 'wp-module-onboarding' ),
			),
			'websiteType' 	=> array(
				"question" 		=> __( '2. What type of website are you making?', 'wp-module-onboarding' ),
				"placeholder" 	=> __( 'e.g. Graphic design portfolio', 'wp-module-onboarding' ),
				"prompt"		=> __( " I am making a website type of", 'wp-module-onboarding' ),
			),
			'uniqueBusiness' => array(
				"question" 		=> __(  "3. Is there anything unique about your business or brand?", 'wp-module-onboarding' ),
				"placeholder"	=> __(  "e.g. Unique product, amazing customer service, customizations, etc.", 'wp-module-onboarding' ),
				"prompt" 		=> __(  "Unique about my business is", 'wp-module-onboarding' ),
			),
		);
		
	}

}
