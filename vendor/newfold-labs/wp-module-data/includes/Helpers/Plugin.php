<?php

namespace NewfoldLabs\WP\Module\Data\Helpers;

/**
 * Helper class for gathering and formatting plugin data
 *
 * @phpstan-type plugin-array array{slug:string, version:string, title:string, url:string, active:bool, mu:bool, auto_updates:bool, users?:array<array{id:int, email:string}>}
 */
class Plugin {
	/**
	 * Prepare plugin data for a single plugin
	 *
	 * @param string $basename The plugin basename (filename relative to WP_PLUGINS_DIR).
	 *
	 * @return plugin-array Hiive relevant plugin details
	 */
	public function collect( $basename ): array {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require wp_normalize_path( constant( 'ABSPATH' ) . '/wp-admin/includes/plugin.php' );
		}

		return $this->get_data( $basename, get_plugin_data( constant( 'WP_PLUGIN_DIR' ) . '/' . $basename ) );
	}

	/**
	 * Prepare plugin data for all plugins
	 *
	 * @return array<plugin-array> of plugins
	 */
	public function collect_installed(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require wp_normalize_path( constant( 'ABSPATH' ) . '/wp-admin/includes/plugin.php' );
		}

		$plugins = array();

		// Collect standard plugins
		foreach ( get_plugins() as $slug => $data ) {
			array_push( $plugins, $this->get_data( $slug, $data ) );
		}

		// Collect mu plugins
		foreach ( get_mu_plugins() as $slug => $data ) {
			array_push( $plugins, $this->get_data( $slug, $data, true ) );
		}

		return $plugins;
	}

	/**
	 * Grab relevant data from plugin data - and only what we want
	 *
	 * @param string $basename The plugin basename (filename relative to WP_PLUGINS_DIR).
	 * @param array  $data The plugin meta-data from its header.
	 * @param bool   $mu   Whether the plugin is installed as a must-use plugin.
	 *
	 * @return plugin-array Hiive relevant plugin details
	 */
	public function get_data( string $basename, array $data, bool $mu = false ): array {
		$plugin                 = array();
		$plugin['slug']         = $basename;
		$plugin['version']      = isset( $data['Version'] ) ? $data['Version'] : '0.0';
		$plugin['title']        = isset( $data['Name'] ) ? $data['Name'] : '';
		$plugin['url']          = isset( $data['PluginURI'] ) ? $data['PluginURI'] : '';
		$plugin['active']       = is_plugin_active( $basename );
		$plugin['mu']           = $mu;
		$plugin['auto_updates'] = ( ! $mu && $this->does_it_autoupdate( $basename ) );

		if ( strpos( $basename, 'jetpack/jetpack.php' ) !== false ) {
			$plugin['users'] = $this->get_admin_users();
		}

		return $plugin;
	}

	/**
	 * Whether the plugin is set to auto update
	 *
	 * @param string $slug Name of the plugin
	 */
	protected function does_it_autoupdate( string $slug ): bool {
		// Check plugin setting for auto updates on all plugins
		if ( 'true' === get_site_option( 'auto_update_plugin', 'true' ) ) {
			return true;
		}

		// check core setting for auto updates on this plugin
		$wp_auto_updates = (array) get_site_option( 'auto_update_plugins', array() );

		return in_array( $slug, $wp_auto_updates, true );
	}

	/**
	 * Get Admin and SuperAdmin user accounts
	 *
	 * @return array<array{id:int, email:string}> $users Array of Admin & Super Admin users
	 */
	protected function get_admin_users(): array {
		// Get all admin users
		$admin_users = get_users(
			array(
				'role' => 'administrator',
			)
		);
		$users       = array();

		// Add administrators to the $users and check for super admin
		foreach ( $admin_users as $user ) {
			$users[] = array(
				'id'    => $user->ID,
				'email' => $user->user_email,
			);
		}

		return $users;
	}
}
