<?php

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\PLS\PLS;

use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {
	add_action(
		'plugins_loaded',
		function () {
			register(
				array(
					'name'     => 'wp-module-pls',
					'label'    => __( 'wp-module-pls', 'wp-module-pls' ),
					'callback' => function ( Container $container ) {
						if ( ! defined( 'NFD_PLS_DIR' ) ) {
							define( 'NFD_PLS_DIR', __DIR__ );
						}
						new PLS( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);
		}
	);

}
