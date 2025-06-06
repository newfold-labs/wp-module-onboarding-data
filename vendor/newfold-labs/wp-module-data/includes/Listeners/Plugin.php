<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

use NewfoldLabs\WP\Module\Data\EventManager;
use NewfoldLabs\WP\Module\Data\Helpers\Transient;
use NewfoldLabs\WP\Module\Data\Helpers\Plugin as PluginHelper;

/**
 * Monitors generic plugin events
 */
class Plugin extends Listener {

	/**
	 * Functions for gathering plugin data
	 *
	 * @var PluginHelper
	 */
	protected $plugin_helper;

	/**
	 * Constructor
	 *
	 * @param EventManager  $manager Event manager instance
	 * @param ?PluginHelper $plugin_helper Class used to fetch plugin data.
	 */
	public function __construct(
		EventManager $manager,
		?PluginHelper $plugin_helper = null
	) {
		parent::__construct( $manager );
		$this->plugin_helper = $plugin_helper ?? new PluginHelper();
	}

	/**
	 * Register the hooks for the subscriber
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Plugin activated/deactivated
		add_action( 'activated_plugin', array( $this, 'activated' ), 10, 2 );
		add_action( 'deactivated_plugin', array( $this, 'deactivated' ), 10, 2 );
		add_action( 'delete_plugin', array( $this, 'save_deleted' ), 10, 2 );
		add_action( 'deleted_plugin', array( $this, 'deleted' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this, 'installed_or_updated' ), 10, 2 );

		// transient found - nfd plugin was just activated, send that event
		if ( Transient::get( 'nfd_plugin_activated' ) ) {
			$this->activated(
				Transient::get( 'nfd_plugin_activated' ),
				false
			);
			Transient::delete( 'nfd_plugin_activated' );
		}
	}

	/**
	 * Plugin activated
	 *
	 * @param string  $plugin Name of the plugin
	 * @param boolean $network_wide Whether plugin was network activated
	 *
	 * @return void
	 */
	public function activated( $plugin, $network_wide ) {
		$data = array(
			'plugin'       => $this->plugin_helper->collect( $plugin ),
			'network_wide' => $network_wide,
		);
		$this->push( 'plugin_activated', $data );
	}

	/**
	 * Plugin deactivated
	 *
	 * @param string  $plugin Name of the plugin
	 * @param boolean $network_wide Whether plugin was network deactivated
	 *
	 * @return void
	 */
	public function deactivated( $plugin, $network_wide ) {
		$data = array(
			'plugin'       => $this->plugin_helper->collect( $plugin ),
			'network_wide' => $network_wide,
		);

		// set the active state to false since it is collected while still active
		$data['plugin']['active'] = false;

		$this->push( 'plugin_deactivated', $data );
	}

	/**
	 * Temporarily store data about the plugin about to be deleted
	 *
	 * @param string $plugin Name of the plugin
	 *
	 * @return void
	 */
	public function save_deleted( $plugin ) {
		update_option( 'deleted_plugin', $this->plugin_helper->collect( $plugin ) );
	}

	/**
	 * Plugin deleted
	 *
	 * @param string  $plugin Name of the plugin
	 * @param boolean $deleted Whether the plugin deletion was successful
	 *
	 * @return void
	 */
	public function deleted( $plugin, $deleted ) {
		// Only send if it was successfully deleted
		if ( $deleted ) {
			$data = array(
				'plugin' => get_option( 'deleted_plugin' ),
			);
			$this->push( 'plugin_deleted', $data );
		}
		// We need to clean up the saved data either way
		delete_option( 'plugin_deleted' );
	}

	/**
	 * Plugin install or update completed
	 *
	 * @param \WP_Upgrader                                                              $wp_upgrader Upgrader object from upgrade hook.
	 * @param array{type:string, action:string, plugins?:array<string>, plugin?:string} $options     Options from upgrade hook including type, action & plugins.
	 *
	 * @hooked upgrader_process_complete
	 * @see \Plugin_Upgrader::bulk_upgrade()
	 */
	public function installed_or_updated( $wp_upgrader, array $options ): void {
		// Bail if not a plugin install or update.
		if ( 'plugin' !== $options['type']
			// Or if the plugins array is set but empty.
			|| ( ! empty( $options['plugins'] ) && empty( $options['plugins'][0] ) ) ) {
			return;
		}

		switch ( $options['action'] ) {
			case 'install':
				$this->installed();
				break;
			case 'update':
				$this->updated( $options );
				break;
		}
	}

	/**
	 * One or more plugins were updated
	 *
	 * @param array{type:string, action:string, plugins?:array<string>, plugin?:string} $options List of update details
	 */
	protected function updated( array $options ): void {
		$plugins = array();

		// Manual updates always return array of plugin slugs
		if ( isset( $options['plugins'] ) && is_array( $options['plugins'] ) ) {
			foreach ( $options['plugins'] as $slug ) {
				array_push( $plugins, $this->plugin_helper->collect( $slug ) );
			}
		}
		// Auto updates always return a single plugin slug
		if ( isset( $options['plugin'] ) ) {
			array_push( $plugins, $this->plugin_helper->collect( $options['plugin'] ) );
		}

		$data = array(
			'plugins' => $plugins,
		);

		$this->push( 'plugin_updated', $data );
	}

	/**
	 * Plugin Installed
	 *
	 * @return void
	 */
	public function installed() {
		$data = array(
			'plugins' => $this->plugin_helper->collect_installed(),
		);
		$this->push( 'plugin_installed', $data );
	}
}
