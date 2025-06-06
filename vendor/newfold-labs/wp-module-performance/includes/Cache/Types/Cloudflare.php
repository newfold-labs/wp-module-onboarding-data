<?php

namespace NewfoldLabs\WP\Module\Performance\Cache\Types;

use NewfoldLabs\WP\Module\Performance\Cache\Purgeable;
use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * Cloudflare cache type.
 */
class Cloudflare extends CacheBase implements Purgeable {

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Cloudflare constructor.
	 *
	 * @param Container $container Dependency injection container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Whether or not the code for this cache type should be loaded.
	 *
	 * @param Container $container Method container.
	 * @return bool True if the cache type should be enabled, false otherwise.
	 */
	public static function should_enable( Container $container ) {
		return (bool) \get_option( 'endurance_cloudflare_enabled', false );
	}

	/**
	 * Check if Cloudflare is enabled.
	 *
	 * @return bool True if Cloudflare is enabled, false otherwise.
	 */
	public function isCoudflareEnabled() {
		return $this->getCloudflareTier() !== 0;
	}

	/**
	 * Get the Cloudflare tier.
	 *
	 * @return int|string The Cloudflare tier.
	 */
	public function getCloudflareTier() {
		$tier = \get_option( 'endurance_cloudflare_enabled', false );

		if ( ! $tier ) {
			return 0;
		}

		switch ( $tier ) {
			case 'hostgator':
				return 'hostgator';
			case 'india':
				return 'india';
			case 'premium':
				return 'premium';
			default:
				return 'basic';
		}
	}

	/**
	 * Purge all Cloudflare cache.
	 */
	public function purge_all() {
		if ( $this->isCoudflareEnabled() ) {
			$this->purgeRequest();
		}
	}

	/**
	 * Purge a URL from Cloudflare cache.
	 *
	 * @param string $url URL to purge.
	 */
	public function purge_url( $url ) {
		if ( $this->isCoudflareEnabled() ) {
			$this->purgeRequest( array( $url ) );
		}
	}

	/**
	 * Purge multiple URLs from the Cloudflare cache.
	 *
	 * @link https://confluence.newfold.com/pages/viewpage.action?spaceKey=UDEV&title=Cache+Purge+API
	 *
	 * @param array $urls URLs to purge.
	 */
	protected function purgeRequest( $urls = array() ) {
		global $wp_version;

		$queryString = http_build_query( array( 'cf' => $this->getCloudflareTier() ), '', '&' );
		$host        = wp_parse_url( \home_url(), PHP_URL_HOST );

		$plugin_brand   = $this->container->plugin()->get( 'id' );
		$plugin_version = $this->container->plugin()->version;

		$headerName = 'X-' . strtoupper( $plugin_brand ) . '-PLUGIN-PURGE';

		$body = array(
			'hosts' => array( $host ),
		);

		if ( $urls ) {
			$body['assets'] = $urls;
		}

		$args = array(
			'body'       => wp_json_encode( $body ),
			'compress'   => true,
			'headers'    => array(
				$headerName    => 1,
				'Content-Type' => 'application/json',
			),
			'sslverify'  => false,
			'user-agent' => "WordPress/{$wp_version}; {$host}; {$plugin_brand}/v{$plugin_version}",
		);

		// If WP_DEBUG is enabled, we want to wait for a response.
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$args['blocking'] = false;
			$args['timeout']  = 0.01;
		}

		wp_remote_post( 'https://cachepurge.bluehost.com/v0/purge?' . $queryString, $args );
	}
}
