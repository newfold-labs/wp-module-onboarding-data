<?php

namespace NewfoldLabs\WP\Module\Performance\Cache\Types;

use NewfoldLabs\WP\Module\Performance\Cache\Purgeable;
use wpscholar\Url;

/**
 * Nginx cache type.
 */
class Nginx extends CacheBase implements Purgeable {

	/**
	 * Purge all assets from the Nginx cache.
	 */
	public function purge_all() {
		$this->purgeRequest();
	}

	/**
	 * Purge the Nginx cache for a specific URL.
	 *
	 * @param string $url The URL to purge.
	 */
	public function purge_url( $url ) {
		$this->purgeRequest( $url );
	}

	/**
	 * Purge the cache.
	 *
	 * @param string $url The URL to purge.
	 */
	protected function purgeRequest( $url = '' ) {
		global $wp_version;

		$url = $url ? new Url( $url ) : new Url( \home_url() );

		$plugin_brand   = $this->getContainer()->plugin()->get( 'id' );
		$plugin_version = $this->getContainer()->plugin()->version;

		$args = array(
			'method'     => 'PURGE',
			'headers'    => array(
				'host' => $url->host,
			),
			'user-agent' => "WordPress/{$wp_version}; {$url->host}; {$plugin_brand}/v{$plugin_version}",
			'sslverify'  => false,
		);

		// If WP_DEBUG is enabled, we want to wait for a response.
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$args['blocking'] = false;
			$args['timeout']  = 0.01;
		}

		$path = '/' . ltrim( $url->path, '/' ) . '.*';

		$http_url = $url::buildUrl(
			array_merge(
				$url->toArray(),
				array(
					'scheme' => 'http',
					'host'   => '127.0.0.1:8080',
					'path'   => $path,
				)
			)
		);

		$https_url = $url::buildUrl(
			array_merge(
				$url->toArray(),
				array(
					'scheme' => 'https',
					'host'   => '127.0.0.1:8443',
					'path'   => $path,
				)
			)
		);

		wp_remote_request( $http_url, $args );
		wp_remote_request( $https_url, $args );
	}
}
