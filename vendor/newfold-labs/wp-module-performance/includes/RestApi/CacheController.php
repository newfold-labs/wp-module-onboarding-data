<?php

namespace NewfoldLabs\WP\Module\Performance\RestApi;

use NewfoldLabs\WP\Module\ECommerce\Permissions;
use NewfoldLabs\WP\Module\Performance\Cache\CacheExclusion;
use NewfoldLabs\WP\Module\Performance\Cache\CacheManager;
use NewfoldLabs\WP\Module\Performance\Cache\CachePurgingService;

use function NewfoldLabs\WP\ModuleLoader\container;
use function NewfoldLabs\WP\Module\Performance\get_cache_level;
use function NewfoldLabs\WP\Module\Performance\get_cache_exclusion;

/**
 * Class CacheExclusionController
 */
class CacheController {
	/**
	 * REST namespace
	 *
	 * @var string
	 */
	protected $namespace = 'newfold-performance/v1';

	/**
	 * REST base
	 *
	 * @var string
	 */
	protected $rest_base = '/cache';

	/**
	 * Registers rest routes for PluginsController class.
	 *
	 * @return void
	 */
	public function register_routes() {

		\register_rest_route(
			$this->namespace,
			$this->rest_base . '/settings',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( Permissions::class, 'rest_is_authorized_admin' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( Permissions::class, 'rest_is_authorized_admin' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'purge_all' ),
					'permission_callback' => array( Permissions::class, 'rest_is_authorized_admin' ),
				),
			)
		);
	}

	/**
	 * Get the settings
	 *
	 * @return \WP_REST_Response
	 */
	public function get_settings() {
		return new \WP_REST_Response(
			array(
				'cacheExclusion' => get_cache_exclusion(),
				'cacheLevel'     => get_cache_level(),
			),
			200
		);
	}

	/**
	 * Update the settings
	 *
	 * @param \WP_REST_Request $request the request.
	 * @return \WP_REST_Response
	 */
	public function update_settings( \WP_REST_Request $request ) {

		if ( $request->has_param( 'cacheExclusion' ) ) {
			$cache_exclusion = $request->get_param( 'cacheExclusion' );
			$result          = update_option( CacheExclusion::OPTION_CACHE_EXCLUSION, $cache_exclusion );
		} elseif ( $request->has_param( 'cacheLevel' ) ) {
			$cache_level = $request->get_param( 'cacheLevel' );
			$result      = update_option( CacheManager::OPTION_CACHE_LEVEL, $cache_level );
		}

		if ( $result ) {
			return new \WP_REST_Response(
				array(
					'result' => true,
				),
				200
			);
		} else {
			return new \WP_REST_Response(
				array(
					'result' => false,
				),
				400
			);
		}
	}

	/**
	 * Clears the entire cache
	 */
	public function purge_all() {

		container()->get( 'cachePurger' )->purge_all();

		return array(
			'status'  => 'success',
			'message' => 'Cache purged',
		);
	}
}
