<?php

namespace NewfoldLabs\WP\Module\Performance\Services;

/**
 * Class for handling internationalization.
 */
class I18nService {
	/**
	 * Version of plugin for versioning the scripts.
	 *
	 * @var version
	 */
	protected $version;

	/**
	 * Init the i18n service
	 *
	 * @param Container $container the container
	 */
	public function __construct( $container ) {
		$this->version = $container->plugin()->version;
		add_action( 'newfold/performance/load_default_translations', array( $this, 'prepare_and_load_js_translations' ) );
		add_action( 'load-toplevel_page_' . $container->plugin()->id, array( $this, 'prepare_and_load_js_translations' ), 1 );
		add_action( 'init', array( $this, 'load_text_domain' ) );
	}

	/**
	 * Load module text domain
	 *
	 * @return void
	 */
	public function load_text_domain() {
		$this::load_php_translations(
			'wp-module-performance',
			NFD_PERFORMANCE_PLUGIN_LANGUAGES_DIR
		);
	}

	/**
	 * Enqueue script for translations of the performance panel settings
	 */
	public function prepare_and_load_js_translations() {
		wp_register_script(
			'wp-module-performance-translations',
			NFD_PERFORMANCE_BUILD_URL . '/translations.min.js',
			array( 'lodash', 'react', 'react-dom', 'wp-data', 'wp-dom-ready', 'wp-element', 'wp-html-entities', 'wp-i18n' ),
			$this->version,
			true
		);

		$this::load_js_translations(
			'wp-module-performance',
			'wp-module-performance-translations',
			NFD_PERFORMANCE_PLUGIN_LANGUAGES_DIR
		);

		wp_enqueue_script( 'wp-module-performance-translations' );
	}

	/**
	 * Loads the PHP translations from .mo files in the languages dir.
	 * The .mo file must be named $domain-$locale.mo
	 *
	 * @param [string] $domain The text domain.
	 * @param [string] $languages_dir The directory containing the .mo files.
	 * @return boolean
	 */
	public static function load_php_translations( $domain, $languages_dir ) {
		$loaded_ptd = load_plugin_textdomain(
			$domain,
			false,
			$languages_dir
		);

		$current_language = get_locale();
		$loaded_td        = load_textdomain( 'wp-module-performance', $languages_dir . '/' . $domain . '-' . $current_language . '.mo' );

		return $loaded_ptd && $loaded_td;
	}

	/**
	 * Localizes a particular script using a JSON file present in the languages dir.
	 * The JSON file must be named $domain-$locale-$script_slug.json.
	 * Note: The script must be registered before this function is called.
	 *
	 * @param [string] $domain The text domain.
	 * @param [string] $script_slug The slug of the registered script.
	 * @param [string] $languages_dir The directory containing the .json file for the script.
	 * @return boolean
	 */
	public static function load_js_translations( $domain, $script_slug, $languages_dir ) {
		return wp_set_script_translations(
			$script_slug,
			$domain,
			$languages_dir
		);
	}
}
