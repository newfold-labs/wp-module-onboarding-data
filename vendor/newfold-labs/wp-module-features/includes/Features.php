<?php
namespace NewfoldLabs\WP\Module\Features;

use NewfoldLabs\WP\Module\Features\Registry;
use NewfoldLabs\WP\Module\Features\FeaturesAPI;

use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * This class Features functionality.
 **/
class Features {

	/**
	 * Singleton instance
	 *
	 * @var Features
	 */
	private static $instance = null;

	/**
	 * Registry
	 *
	 * @var Registry
	 */
	private static $registry = null;

	/**
	 * Module Path
	 *
	 * @var String
	 */
	private static $module_path = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// Create registry
		self::$registry = new Registry();

		self::$module_path = container()->plugin()->url . 'vendor/newfold-labs/wp-module-features';

		if ( function_exists( 'add_action' ) ) {

			// Find and add all features to registry
			add_action( 'plugins_loaded', array( __CLASS__, 'initFeatures' ), 3 );

			// Register API endpoints
			add_action( 'rest_api_init', array( __CLASS__, 'registerRoutes' ) );

			// Add CLI commands
			add_action( 'cli_init', array( __CLASS__, 'registerCLI' ) );

			// Register API script and localized values
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );

			\add_action( 'init', array( __CLASS__, 'load_text_domain' ), 100 );
		}

		// Add default filter to make any feature null value return false
		if ( function_exists( 'add_filter' ) ) {
			add_filter(
				'newfold/features/filter/isEnabled',
				array( __CLASS__, 'defaultIsEnabledFilter' ),
				99, // low priority so modules can override easily if needed
				1
			);
		}
	}

	/**
	 * Find and add all feature classes to the registry
	 */
	public static function initFeatures() {
		// Find extended instances of the Feature class and add to the Registry
		$features = apply_filters( 'newfold/features/filter/register', array() );
		foreach ( $features as $feature ) {
			// Validate that the feature extends this
			if ( is_subclass_of( $feature, 'NewfoldLabs\WP\Module\Features\Feature' ) ) {
				// add class to registry and instantiate
				self::$registry->set( $feature );
			}
		}
	}

	/**
	 * Register routes
	 */
	public static function registerRoutes() {
		new FeaturesAPI();
	}

	/**
	 * Add CLI commands
	 */
	public static function registerCLI() {
		\WP_CLI::add_command(
			'newfold features',
			'NewfoldLabs\WP\Module\Features\FeaturesCLI',
			array(
				'shortdesc' => 'Operations for Newfold Features.',
				'longdesc'  => 'Internal commands to handle features.' .
								PHP_EOL . 'Subcommands: all, enable, disable, isEnabled.',
			)
		);
	}

	/**
	 * Register API script and localized values
	 */
	public static function assets() {

		// Register features API script
		$scriptPath = container()->plugin()->url . 'vendor/newfold-labs/wp-module-features/static/js/features.js';
		wp_register_script(
			'newfold-features',
			$scriptPath,
			array( 'wp-api-fetch', 'wp-i18n' ),
			container()->plugin()->version,
			true
		);

		// Localized Script with initial feature values
		wp_localize_script(
			'newfold-features',
			'NewfoldFeatures',
			array(
				'features'  => self::getFeatures(),
				'togglable' => self::getToggleableFeatures(),
				'restUrl'   => esc_url_raw( rest_url() ) . 'newfold-features/v1',
			)
		);

		\wp_set_script_translations(
			'newfold-features',
			'wp-module-features',
			self::$module_path . '/languages'
		);
	}
	/**
	 * Add default filter to make any feature null value return false
	 *
	 * @param boolean $value The value to set.
	 */
	public static function defaultIsEnabledFilter( $value ) {
		// if feature state is null, return false
		if ( ! isset( $value ) ) {
			$value = false;
		}
		return $value;
	}

	/**
	 * Get instance for singleton Features
	 *
	 * @return Features instance
	 */
	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new Features();
		}
		return self::$instance;
	}

	/**
	 * Get All Features and filter states
	 *
	 * @return Array list of features and enabled states (key:name value:enabled)
	 */
	public static function getFeatures() {
		$features = array();
		foreach ( self::getFeatureList() as $feature ) {
			$features[ $feature ] = Features::getInstance()->getFeature( $feature )->isEnabled();
		}
		return $features;
	}

	/**
	 * Get All Features and filter states
	 *
	 * @return Array list of features and enabled states (key:name value:enabled)
	 */
	public static function getToggleableFeatures() {
		$features = array();
		foreach ( self::getFeatureList() as $feature ) {
			$features[ $feature ] = Features::getInstance()->getFeature( $feature )->isTogglable();
		}
		return $features;
	}

	/**
	 * Get feature list
	 *
	 * @return Array list of features
	 */
	public static function getFeatureList() {
		return self::$registry->keys();
	}

	/**
	 * Get Feature
	 *
	 * @param string $name the name of the feature
	 * @return Feature instance
	 */
	public static function getFeature( $name ) {
		return self::$registry->get( $name );
	}

	/**
	 * Has Feature
	 *
	 * @param string $name the name of the feature
	 * @return boolean true if feature exists, false otherwise
	 */
	public static function hasFeature( $name ) {
		return self::$registry->has( $name );
	}

	/**
	 * Load text domain for Module
	 *
	 * @return void
	 */
	public static function load_text_domain() {

		$module_path = container()->plugin()->url . 'vendor/newfold-labs/wp-module-features';

		\load_plugin_textdomain(
			'wp-module-features',
			false,
			$module_path . '/languages'
		);

		\load_script_textdomain(
			'newfold-features',
			'wp-module-features',
			self::$module_path . '/languages'
		);
	}
}
