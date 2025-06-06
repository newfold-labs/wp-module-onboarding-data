<?php
namespace NewfoldLabs\WP\Module\Performance\Cache;

use NewfoldLabs\WP\ModuleLoader\Container;

use function NewfoldLabs\WP\Module\Performance\get_default_cache_exclusions;

/**
 * Cache Exclusion Class
 */
class CacheExclusion {
	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Option used to store all pages should be excluded from cache.
	 *
	 * @var string
	 */
	const OPTION_CACHE_EXCLUSION = 'nfd_performance_cache_exclusion';

	/**
	 * Constructor.
	 *
	 * @param Container $container the container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;

		add_filter( 'newfold-runtime', array( $this, 'add_to_runtime' ) );
	}
	/**
	 * Add values to the runtime object.
	 *
	 * @param array $sdk The runtime object.
	 *
	 * @return array
	 */
	public function add_to_runtime( $sdk ) {
		return array_merge( $sdk, array( 'cacheExclusion' => get_option( self::OPTION_CACHE_EXCLUSION, get_default_cache_exclusions() ) ) );
	}
}
