<?php

use NewfoldLabs\WP\Module\AI\AI;
use NewfoldLabs\WP\ModuleLoader\Container;

use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {
			// Set Global Constants
			if ( ! defined( 'NFD_MODULE_AI_DIR' ) ) {
				define( 'NFD_MODULE_AI_DIR', __DIR__ );
			}

			if ( ! defined( 'NFD_AI_SERVICE_BASE' ) ) {
				define( 'NFD_AI_SERVICE_BASE', 'https://hiive.cloud/workers/ai-proxy/v1/' );
				define( 'NFD_AI_BASE', 'https://hiive.cloud/workers/ai-sitegen-proxy/' );
				define( 'NFD_PATTERNS_BASE', 'https://patterns.hiive.cloud/' );
				define( 'NFD_SITEGEN_OPTION', 'nfd-ai-site-gen' );
			}

			register(
				array(
					'name'     => 'ai',
					'label'    => __( 'ai', 'newfold-ai-module' ),
					'callback' => function ( Container $container ) {
						return new AI( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);

			// Register the custom command with WP_CLI
			if ( defined( '\\WP_CLI' ) && \WP_CLI ) {
				require_once __DIR__ . '/includes/NFD_CLI.php';
				WP_CLI::add_command( 'newfold', 'NFD_CLI' );
			}
		}
	);

}
