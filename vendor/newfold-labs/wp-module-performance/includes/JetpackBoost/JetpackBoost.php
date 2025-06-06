<?php

namespace NewfoldLabs\WP\Module\Performance\JetpackBoost;

use NewfoldLabs\WP\ModuleLoader\Container;

use NewfoldLabs\WP\Module\Installer\Services\PluginInstaller;
use Automattic\Jetpack\My_Jetpack\Products\Boost;

/**
 * Handles link prefetch functionality.
 */
class JetpackBoost {

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container The dependency injection container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;

		add_filter( 'newfold-runtime', array( $this, 'add_to_runtime' ), 100 );

		add_action( 'admin_head', array( $this, 'prefetch_jetpack_boost' ) );

		// Set default values for JetPack Boost on fresh installation.
		add_action( 'admin_init', array( $this, 'handle_jetpack_boost_default_values' ) );
	}

	/**
	 * Add to Newfold SDK runtime.
	 *
	 * @param array $sdk SDK data.
	 * @return array SDK data.
	 */
	public function add_to_runtime( $sdk ) {
		$is_jetpack_boost_enabled   = is_plugin_active( 'jetpack-boost/jetpack-boost.php' );
		$jetpack_options            = get_option( 'jetpack_options', false );
		$is_jetpack_boost_connected = is_array( $jetpack_options ) && isset( $jetpack_options['id'] ) ? true : false;

		$values = array(
			'is_active'                 => $is_jetpack_boost_enabled,
			'jetpack_premium_is_active' => $this->is_jetpackpremium_active(),
			'critical_css'              => $is_jetpack_boost_enabled ? get_option( 'jetpack_boost_status_critical-css' ) : false,
			'blocking_js'               => $is_jetpack_boost_enabled ? get_option( 'jetpack_boost_status_render-blocking-js' ) : false,
			'minify_js'                 => $is_jetpack_boost_enabled ? get_option( 'jetpack_boost_status_minify-js', false ) : false,
			'minify_js_excludes'        => implode( ',', get_option( 'jetpack_boost_ds_minify_js_excludes', array( 'jquery', 'jquery-core', 'underscore', 'backbone' ) ) ),
			'minify_css'                => $is_jetpack_boost_enabled ? get_option( 'jetpack_boost_status_minify-css', false ) : false,
			'minify_css_excludes'       => implode( ',', get_option( 'jetpack_boost_ds_minify_css_excludes', array( 'admin-bar', 'dashicons', 'elementor-app' ) ) ),
			'install_token'             => PluginInstaller::rest_get_plugin_install_hash(),
			'jetpack_boost_connected'   => $is_jetpack_boost_connected,
		);

		return array_merge( $sdk, array( 'jetpackboost' => $values ) );
	}

	/**
	 * Check if Jetpack Boost premium is active.
	 *
	 * @return boolean
	 */
	public function is_jetpackpremium_active() {
		if ( ! class_exists( Boost::class ) ) {
			return false;
		}

		$info = Boost::get_info();
		return array_key_exists( 'is_upgradable', $info )
			? ! $info['is_upgradable']
			: false;
	}

	/**
	 * Prefetch for JetPack Boost page.
	 *
	 * @return void
	 */
	public function prefetch_jetpack_boost() {
		if ( is_plugin_active( 'jetpack-boost/jetpack-boost.php' ) ) {
			$admin_url = admin_url( 'admin.php?page=jetpack-boost' );
			echo '<link rel="prefetch" href="' . esc_url( $admin_url ) . '">' . "\n";
		}
	}

	/**
	 * Set default values for JetPack Boost.
	 *
	 * @return void
	 */
	public function handle_jetpack_boost_default_values() {
		if ( $this->container->has( 'isFreshInstallation' ) && $this->container->get( 'isFreshInstallation' ) && is_plugin_active( 'jetpack-boost/jetpack-boost.php' ) ) {
			update_option( 'jetpack_boost_status_render-blocking-js', true );
		}
	}
}
