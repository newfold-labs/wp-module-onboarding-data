<?php

namespace NewfoldLabs\WP\Module\Performance\Cache;

use NewfoldLabs\WP\Module\Performance\Cache\Types\CacheBase;
use NewfoldLabs\WP\ModuleLoader\Container;
use WP_Forge\Collection\Collection;

/**
 * Cache manager.
 */
class CacheManager {

	/**
	 * The option name where the cache level is stored.
	 *
	 * @var string
	 */
	public const OPTION_CACHE_LEVEL = 'newfold_cache_level';

	/**
	 * Allowed cache level values.
	 *
	 * @var array
	 */
	public const VALID_CACHE_LEVELS = array( 0, 1, 2, 3 );

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container the container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Map of cache types to class names.
	 *
	 * @return string[]
	 */
	protected function classMap() {
		return array(
			'browser'    => __NAMESPACE__ . '\\Types\\Browser',
			'cloudflare' => __NAMESPACE__ . '\\Types\\Cloudflare',
			'file'       => __NAMESPACE__ . '\\Types\\File',
			'nginx'      => __NAMESPACE__ . '\\Types\\Nginx',
			'sitelock'   => __NAMESPACE__ . '\\Types\\Sitelock',
		);
	}

	/**
	 * Get a list of registered cache types.
	 *
	 * @return string[]
	 */
	public function registered_cache_types() {
		return array_keys( $this->classMap() );
	}

	/**
	 * Get a list of enabled cache types.
	 *
	 * @return array
	 */
	public function enabled_cache_types() {
		$default_cache_types = array( 'browser' );

		if ( $this->container->has( 'cache_types' ) ) {
			$provided_types = $this->container->get( 'cache_types' );
		} else {
			$provided_types = $default_cache_types;
		}

		return is_array( $provided_types )
		? array_intersect( array_map( 'strtolower', $provided_types ), $this->registered_cache_types() )
		: $default_cache_types;
	}


	/**
	 * Get an array of page cache type instances based on the enabled cache types.
	 *
	 * @return CacheBase[] An array of cache type instances.
	 */
	public function get_instances() {
		$instances  = array();
		$collection = new Collection( $this->classMap() );
		$map        = $collection->only( $this->enabled_cache_types() );
		foreach ( $map as $type => $class ) {
			/**
			 * CacheBase instance.
			 *
			 * @var CacheBase $class
			 */
			if ( $class::should_enable( $this->container ) ) {
				$instances[ $type ] = new $class();
				$instances[ $type ]->setContainer( $this->container );
			}
		}

		return $instances;
	}
}
