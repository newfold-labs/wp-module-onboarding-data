<?php

namespace NewfoldLabs\WP\Module\Performance\Cache;

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\Performance\OptionListener;
use NewfoldLabs\WP\Module\Performance\Cache\Types\Browser;
use NewfoldLabs\WP\Module\Performance\Cache\Types\File;

use function NewfoldLabs\WP\Module\Performance\get_cache_level;

/**
 * Add activation/deactivation hooks for the performance feature.
 **/
class CacheFeatureHooks {

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( function_exists( 'add_action' ) ) {
			add_action( 'newfold_container_set', array( $this, 'plugin_hooks' ) );
			add_action( 'plugins_loaded', array( $this, 'hooks' ) );
			new OptionListener( CacheManager::OPTION_CACHE_LEVEL, array( $this, 'on_cache_level_change' ) );
		}
	}

	/**
	 * Hooks for plugin activation/deactivation
	 *
	 * @param Container $container from the plugin
	 */
	public function plugin_hooks( Container $container ) {
		$this->container = $container;
		register_activation_hook( $container->plugin()->file, array( $this, 'on_activation' ) );
		register_deactivation_hook( $container->plugin()->file, array( $this, 'on_deactivation' ) );
	}

	/**
	 * Add hooks.
	 */
	public function hooks() {
		add_action( 'newfold/features/action/onEnable:performance', array( $this, 'on_activation' ) );
		add_action( 'newfold/features/action/onDisable:performance', array( $this, 'on_deactivation' ) );
	}

	/**
	 * Activation hook to perform when plugin is activated or feature is enabled
	 */
	public function on_activation() {
		File::on_activation();
		Browser::on_activation();
		// Add headers to .htaccess
		$responseHeaderManager = new ResponseHeaderManager();
		$responseHeaderManager->add_header( 'X-Newfold-Cache-Level', absint( get_cache_level() ) );
	}

	/**
	 * Deactivation hook to perform when plugin is deactivated or feature is disabled
	 */
	public function on_deactivation() {
		File::on_deactivation();
		Browser::on_deactivation();
		// Remove all headers from .htaccess
		$responseHeaderManager = new ResponseHeaderManager();
		$responseHeaderManager->remove_all_headers();
	}

	/**
	 * On cache level change, update the response headers.
	 *
	 * @param int|null $cacheLevel The cache level.
	 */
	public function on_cache_level_change( $cacheLevel ) {
		/**
		 * Respone Header Manager from container
		 *
		 * @var ResponseHeaderManager $responseHeaderManager
		 */
		$responseHeaderManager = $this->container->get( 'responseHeaderManager' );
		$responseHeaderManager->add_header( 'X-Newfold-Cache-Level', absint( $cacheLevel ) );

		// Remove the old option from EPC, if it exists.
		if ( $this->container->get( 'hasMustUsePlugin' ) && absint( get_option( 'endurance_cache_level', 0 ) ) ) {
			update_option( 'endurance_cache_level', 0 );
			delete_option( 'endurance_cache_level' );
		}
	}
}
