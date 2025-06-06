<?php

use NewfoldLabs\WP\Module\Data\Data;
use NewfoldLabs\WP\Module\Data\Helpers\Encryption;
use NewfoldLabs\WP\Module\Data\Helpers\Transient;
use NewfoldLabs\WP\Module\Data\SiteCapabilities;
use NewfoldLabs\WP\ModuleLoader\Container;
use WP_Forge\UpgradeHandler\UpgradeHandler;
use function NewfoldLabs\WP\ModuleLoader\container;

use function NewfoldLabs\WP\ModuleLoader\register as registerModule;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Do not allow multiple copies of the module to be active
if ( defined( 'NFD_DATA_MODULE_VERSION' ) ) {
	return;
}

define( 'NFD_DATA_MODULE_VERSION', '2.6.11' );

if ( function_exists( 'is_admin' ) && is_admin() ) {
	$upgrade_handler = new UpgradeHandler(
		__DIR__ . '/upgrades',
		get_option( 'nfd_data_module_version' ),
		NFD_DATA_MODULE_VERSION
	);

	if ( $upgrade_handler->maybe_upgrade() ) {
		// If an upgrade occurred, update the new version in the database to prevent running the routine(s) again.
		update_option( 'nfd_data_module_version', NFD_DATA_MODULE_VERSION, true );
	}
}

/**
 * Register the data module
 */
if ( function_exists( 'add_action' ) && function_exists( 'add_filter' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			registerModule(
				array(
					'name'     => 'data',
					'label'    => __( 'Data', 'newfold-data-module' ),
					'callback' => function () {
						$module = new Data();
						$module->start();
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);
		}
	);

	// Auto-encrypt token on save.
	add_filter(
		'pre_update_option_nfd_data_token',
		function ( $value ) {
			return ( new Encryption() )->encrypt( $value );
		}
	);

	// Auto-decrypt token when fetched
	add_filter(
		'option_nfd_data_token',
		function ( $value ) {
			return ( new Encryption() )->decrypt( $value );
		}
	);

	// Temporary filter, as the migrate capability isn't working as expected.
	add_filter(
		'pre_set_transient_nfd_site_capabilities',
		function ( $transient ) {
			if ( empty( $transient ) && 'bluehost' === container()->plugin()->brand ) {
				return array(
					'canMigrateSite' => true,
					'hasAISiteGen'   => true,
				);
			}
			return $transient;
		},
		10,
		2
	);

	// Register activation/deactivation hooks
	add_action(
		'newfold_container_set',
		function ( Container $container ) {

			register_activation_hook(
				$container->plugin()->file,
				function () use ( $container ) {
					nfd_create_event_queue_table();
					Transient::set( 'nfd_plugin_activated', $container->plugin()->basename );
				}
			);

			register_deactivation_hook(
				$container->plugin()->file,
				function () use ( $container ) {
					delete_option( 'nfd_data_module_version' );
					nfd_drop_event_queue_table();
				}
			);

			$container->set(
				'capabilities',
				$container->service(
					function () {
						return new SiteCapabilities();
					}
				)
			);
		}
	);

}

/**
 * Create the event queue table
 */
function nfd_create_event_queue_table() {
	global $wpdb;

	if ( ! function_exists( 'dbDelta' ) ) {
		require ABSPATH . 'wp-admin/includes/upgrade.php';
	}

	$wpdb->hide_errors();

	$charset_collate = $wpdb->get_charset_collate();

	$sql = <<<SQL
CREATE TABLE {$wpdb->prefix}nfd_data_event_queue (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	event longtext NOT NULL,
	attempts tinyint(3) NOT NULL DEFAULT 0,
	reserved_at datetime DEFAULT NULL,
	available_at datetime NOT NULL,
	created_at datetime NOT NULL,
	PRIMARY KEY (id)
	) $charset_collate;
SQL;

	dbDelta( $sql );
}

/**
 * Drop the event queue table
 */
function nfd_drop_event_queue_table() {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}nfd_data_event_queue" );
}
