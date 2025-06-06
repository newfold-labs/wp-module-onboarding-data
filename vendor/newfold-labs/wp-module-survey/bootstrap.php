<?php

use NewfoldLabs\WP\Module\Survey\Data\Constants;
use NewfoldLabs\WP\Module\Survey\Survey;
use NewfoldLabs\WP\Module\Survey\Service;
use NewfoldLabs\WP\ModuleLoader\Container;

use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {
	add_action(
		'plugins_loaded',
		function () {
			register(
				array(
					'name'     => 'wp-module-survey',
					'label'    => __( 'wp-module-survey', 'wp-module-survey' ),
					'callback' => function ( Container $container ) {
						new Constants( $container );
						$container->set(
							'survey',
							$container->service(
								function () {
									return new Service();
								}
							)
						);
						new Survey( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);
		}
	);

}
