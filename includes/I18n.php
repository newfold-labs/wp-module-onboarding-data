<?php
namespace NewfoldLabs\WP\Module\Onboarding\Data;

use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * Class for handling internationalization.
 */
class I18n {
	/**
	 * I18n constructor
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'load_php_textdomain' ) );
	}

	/**
	 * Loads the textdomain for the module. This applies only to PHP strings.
	 *
	 * @return boolean
	 */
	public static function load_php_textdomain() {
		if ( is_admin() || ( is_user_logged_in() && current_user_can( 'manage_options' ) ) ) {
			return load_plugin_textdomain(
				'wp-module-onboarding-data',
				false,
				dirname( container()->plugin()->basename ) . '/vendor/newfold-labs/wp-module-onboarding-data/languages'
			);
		}
	}
}

// Instantiate the internationalization class for only Admins.
if ( is_callable( 'add_action' ) ) {
	new I18n();
}
