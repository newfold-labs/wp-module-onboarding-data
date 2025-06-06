<?php

namespace NewfoldLabs\WP\Module\Performance;

use NewfoldLabs\WP\Module\Performance\HealthChecks\AutosaveIntervalHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\BrowserCachingHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\CloudflareHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\ConcatenateCssHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\ConcatenateJsHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\CronLockTimeoutHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\DeferNonEssentialJsHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\EmptyTrashDaysHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\LazyLoadingHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\LinkPrefetchHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\PageCachingHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\PermalinksHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\PersistentObjectCacheHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\PostRevisionsHealthCheck;
use NewfoldLabs\WP\Module\Performance\HealthChecks\PrioritizeCssHealthCheck;

/**
 * Add performance health checks.
 */
class HealthChecks {
	/**
	 * Container.
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container Container.
	 */
	public function __construct( $container ) {
		$this->container = $container;
		if ( function_exists( 'add_filter' ) ) {
			$this->add_hooks();
		}
	}

	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		add_filter( 'site_status_tests', array( $this, 'add_health_checks' ) );
	}

	/**
	 * Add health checks.
	 *
	 * @param array $tests Site status tests.
	 *
	 * @return array Site status tests.
	 */
	public function add_health_checks( $tests ) {
		$health_checks = array(
			new AutosaveIntervalHealthCheck(),
			new PostRevisionsHealthCheck(),
			new AutosaveIntervalHealthCheck(),
			new BrowserCachingHealthCheck(),
			new CloudflareHealthCheck(),
			new ConcatenateCssHealthCheck(),
			new ConcatenateJsHealthCheck(),
			new CronLockTimeoutHealthCheck(),
			new DeferNonEssentialJsHealthCheck(),
			new EmptyTrashDaysHealthCheck(),
			new LazyLoadingHealthCheck(),
			new LinkPrefetchHealthCheck(),
			new PageCachingHealthCheck(),
			new PermalinksHealthCheck(),
			new PostRevisionsHealthCheck(),
			new PrioritizeCssHealthCheck(),
		);

		// Override the core persistent object cache health check, but only for Bluehost.
		if ( 'bluehost' === $this->container->plugin()->brand ) {
			$health_checks[] = new PersistentObjectCacheHealthCheck();
		}

		foreach ( $health_checks as $health_check ) {
			$tests = $health_check->register_health_check( $tests );
		}

		return $tests;
	}
}
