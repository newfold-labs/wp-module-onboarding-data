<?php

namespace NewfoldLabs\WP\Module\Performance\Cache\Types;

use NewfoldLabs\WP\Module\Performance\Cache\Purgeable;
use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * Sitelock cache type.
 */
class Sitelock extends CacheBase implements Purgeable {

	/**
	 * Whether the code for this cache type should be loaded.
	 *
	 * @param Container $container The dependency injection container.
	 *
	 * @return bool True if the code should be loaded, false otherwise.
	 */
	public static function should_enable( Container $container ) {
		return (bool) \get_option( 'endurance_sitelock_enabled', false );
	}

	/**
	 * Purge all content from the Sitelock CDN cache.
	 */
	public function purge_all() {

		$refresh_token = \get_option( '_mm_refresh_token' );

		if ( false === $refresh_token ) {
			return;
		}

		$endpoint = 'https://my.bluehost.com/cgi/wpapi/cdn_purge';
		$domain   = wp_parse_url( \home_url(), PHP_URL_HOST );
		$query    = add_query_arg( array( 'domain' => $domain ), $endpoint );

		$path = ABSPATH;
		$path = explode( 'public_html/', $path );
		if ( 2 === count( $path ) ) {
			$path = '/public_html/' . $path[1];
		} else {
			return;
		}

		$args = array(
			'headers' => array(
				'x-api-refresh-token' => $refresh_token,
				'x-api-path'          => bin2hex( $path ),
			),
		);

		// If WP_DEBUG is enabled, we want to wait for a response.
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$args['blocking'] = false;
			$args['timeout']  = 0.01;
		}

		wp_remote_get( $query, $args );
	}

	/**
	 * Purge a specific URL from the Sitelock CDN cache.
	 *
	 * @param string $url The URL to purge.
	 */
	public function purge_url( $url ) {

		$refreshToken = \get_option( '_mm_refresh_token' );

		if ( false === $refreshToken ) {
			return;
		}

		$path    = wp_parse_url( $url, PHP_URL_PATH );
		$pattern = rawurlencode( $path . '$' );
		$domain  = wp_parse_url( \home_url(), PHP_URL_HOST );

		$args = array(
			'method'  => 'PUT',
			'headers' => array(
				'X-MOJO-TOKEN' => $refreshToken,
			),
		);

		// If WP_DEBUG is enabled, we want to wait for a response.
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$args['blocking'] = false;
			$args['timeout']  = 0.01;
		}

		wp_remote_post( "https://my.bluehost.com/api/domains/{$domain}/caches/sitelock/{$pattern}", $args );
	}
}
