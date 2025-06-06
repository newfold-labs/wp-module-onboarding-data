<?php

use NewfoldLabs\WP\ModuleLoader\Container;

add_action(
	'newfold_container_set',
	function ( Container $container ) {
		$brand_code = array(
			'bluehost'      => '86241',
			'hostgator'     => '57686',
			'web'           => '86239',
			'crazy-domains' => '57687',
			'default'       => '86240',
		);
		$brand      = $container->plugin()->id;
		if ( is_plugin_active( 'jetpack/jetpack.php' ) ) {
			if ( empty( $brand ) || ! array_key_exists( $brand, $brand_code ) ) {
					$brand = 'default';
			}
			$jetpack_affiliate_code = get_option( 'jetpack_affiliate_code' );
			! $jetpack_affiliate_code && update_option( 'jetpack_affiliate_code', $brand_code[ $brand ] );
		}
	}
);
