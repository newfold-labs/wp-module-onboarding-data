<?php

namespace NewfoldLabs\WP\Module\Performance\Cache\Types;

use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * Base class for cache types.
 */
abstract class CacheBase {

	/**
	 * Dependency injection container.
	 *
	 * @var Container $container
	 */
	protected $container;

	/**
	 * Whether or not the code for this cache type should be loaded.
	 *
	 * @param Container $container Dependency injection container.
	 *
	 * @return bool True if the cache type should be enabled, false otherwise.
	 */
	public static function should_enable( Container $container ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return true;
	}

	/**
	 * Set the dependency injection container
	 *
	 * @param Container $container Dependency injection container
	 *
	 * @return void
	 */
	public function setContainer( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Get the dependency injection container.
	 *
	 * @return Container
	 */
	public function getContainer() {
		return $this->container;
	}
}
