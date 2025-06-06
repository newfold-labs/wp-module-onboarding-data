<?php

namespace NewfoldLabs\WP\Module\Performance\HealthChecks;

use WP_Mock;
use WP_Mock\Tools\TestCase;
use Patchwork;

/**
 * Test health checks.
 */
class HealthChecksTest extends TestCase {

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		WP_Mock::setUp();
		Patchwork\restoreAll(); // Ensure Patchwork starts with a clean slate.

		WP_Mock::passthruFunction( '__' );
		WP_Mock::passthruFunction( 'esc_html__' );
	}

	/**
	 * Tear down the test environment.
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
		Patchwork\restoreAll(); // Clean up all redefined functions/constants.
	}

	/**
	 * Test AutosaveIntervalHealthCheck.
	 */
	public function test_autosave_interval_health_check() {
		Patchwork\redefine(
			'defined',
			function ( $constant_name ) {
				if ( 'AUTOSAVE_INTERVAL' === $constant_name ) {
					return true;
				}
				return Patchwork\relay();
			}
		);

		Patchwork\redefine(
			'constant',
			function ( $constant_name ) {
				if ( 'AUTOSAVE_INTERVAL' === $constant_name ) {
					return 30;
				}
				return Patchwork\relay();
			}
		);

		$health_check = new AutosaveIntervalHealthCheck();
		$this->assertTrue( $health_check->test(), 'Autosave interval should pass when set to 30 seconds or more.' );

		Patchwork\redefine(
			'constant',
			function ( $constant_name ) {
				if ( 'AUTOSAVE_INTERVAL' === $constant_name ) {
					return 10;
				}
				return Patchwork\relay();
			}
		);

		$this->assertFalse( $health_check->test(), 'Autosave interval should fail when set to less than 30 seconds.' );
	}

	/**
	 * Test PostRevisionsHealthCheck.
	 */
	public function test_post_revisions_health_check() {
		Patchwork\redefine(
			'defined',
			function ( $constant_name ) {
				if ( 'WP_POST_REVISIONS' === $constant_name ) {
					return true;
				}
				return Patchwork\relay();
			}
		);

		Patchwork\redefine(
			'constant',
			function ( $constant_name ) {
				if ( 'WP_POST_REVISIONS' === $constant_name ) {
					return 5;
				}
				return Patchwork\relay();
			}
		);

		$health_check = new PostRevisionsHealthCheck();
		$this->assertTrue( $health_check->test(), 'Post revisions should pass when limited to 5 or less.' );

		Patchwork\redefine(
			'constant',
			function ( $constant_name ) {
				if ( 'WP_POST_REVISIONS' === $constant_name ) {
					return 10;
				}
				return Patchwork\relay();
			}
		);

		$this->assertFalse( $health_check->test(), 'Post revisions should fail when set to more than 5.' );
	}

	/**
	 * Test EmptyTrashDaysHealthCheck.
	 */
	public function test_empty_trash_days_health_check() {
		Patchwork\redefine(
			'defined',
			function ( $constant_name ) {
				if ( 'EMPTY_TRASH_DAYS' === $constant_name ) {
					return true;
				}
				return Patchwork\relay();
			}
		);

		Patchwork\redefine(
			'constant',
			function ( $constant_name ) {
				if ( 'EMPTY_TRASH_DAYS' === $constant_name ) {
					return 30;
				}
				return Patchwork\relay();
			}
		);

		$health_check = new EmptyTrashDaysHealthCheck();
		$this->assertTrue( $health_check->test(), 'Empty trash days should pass when set to 30 days or less.' );

		Patchwork\redefine(
			'constant',
			function ( $constant_name ) {
				if ( 'EMPTY_TRASH_DAYS' === $constant_name ) {
					return 31;
				}
				return Patchwork\relay();
			}
		);

		$this->assertFalse( $health_check->test(), 'Empty trash days should fail when set to more than 30 days.' );
	}

	/**
	 * Test BrowserCachingHealthCheck.
	 */
	public function test_browser_caching_health_check() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'newfold_cache_level' )
			->once()
			->andReturn( 2 );

		$health_check = new BrowserCachingHealthCheck();
		$this->assertTrue( $health_check->test(), 'Browser caching should pass when newfold_cache_level is 1 or more.' );

		WP_Mock::userFunction( 'get_option' )
			->with( 'newfold_cache_level' )
			->once()
			->andReturn( 0 );

		$this->assertFalse( $health_check->test(), 'Browser caching should fail when newfold_cache_level is less than 1.' );
	}

	/**
	 * Test LazyLoadingHealthCheck.
	 */
	public function test_lazy_loading_health_check() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'nfd_image_optimization', array() )
			->once()
			->andReturn( array( 'lazy_loading' => array( 'enabled' => true ) ) );

		$health_check = new LazyLoadingHealthCheck();
		$this->assertTrue( $health_check->test(), 'Lazy loading should pass when enabled.' );

		WP_Mock::userFunction( 'get_option' )
			->with( 'nfd_image_optimization', array() )
			->once()
			->andReturn( array( 'lazy_loading' => array( 'enabled' => false ) ) );

		$this->assertFalse( $health_check->test(), 'Lazy loading should fail when not enabled.' );
	}

	/**
	 * Test LinkPrefetchHealthCheck.
	 */
	public function test_link_prefetch_health_check() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'nfd_link_prefetch_settings', array() )
			->once()
			->andReturn( array( 'activeOnDesktop' => true ) );

		$health_check = new LinkPrefetchHealthCheck();
		$this->assertTrue( $health_check->test(), 'Link prefetch should pass when active on desktop.' );

		WP_Mock::userFunction( 'get_option' )
			->with( 'nfd_link_prefetch_settings', array() )
			->once()
			->andReturn( array( 'activeOnDesktop' => false ) );

		$this->assertFalse( $health_check->test(), 'Link prefetch should fail when not active on desktop.' );
	}

	/**
	 * Test PageCachingHealthCheck.
	 */
	public function test_page_caching_health_check() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'newfold_cache_level' )
			->once()
			->andReturn( 2 );

		$health_check = new PageCachingHealthCheck();
		$this->assertTrue( $health_check->test(), 'Page caching should pass when newfold_cache_level is 2 or more.' );

		WP_Mock::userFunction( 'get_option' )
			->with( 'newfold_cache_level' )
			->once()
			->andReturn( 0 );

		$this->assertFalse( $health_check->test(), 'Page caching should fail when newfold_cache_level is less than 2.' );
	}

	/**
	 * Test ConcatenateCssHealthCheck.
	 */
	public function test_concatenate_css_health_check() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'jetpack_boost_status_minify-css', false )
			->once()
			->andReturn( true );

		$health_check = new ConcatenateCssHealthCheck();
		$this->assertTrue( $health_check->test(), 'CSS concatenation should pass when enabled.' );

		WP_Mock::userFunction( 'get_option' )
			->with( 'jetpack_boost_status_minify-css', false )
			->once()
			->andReturn( false );

		$this->assertFalse( $health_check->test(), 'CSS concatenation should fail when not enabled.' );
	}

	/**
	 * Test ConcatenateJsHealthCheck.
	 */
	public function test_concatenate_js_health_check() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'jetpack_boost_status_minify-js', false )
			->once()
			->andReturn( true );

		$health_check = new ConcatenateJsHealthCheck();
		$this->assertTrue( $health_check->test(), 'JS concatenation should pass when enabled.' );

		WP_Mock::userFunction( 'get_option' )
			->with( 'jetpack_boost_status_minify-js', false )
			->once()
			->andReturn( false );

		$this->assertFalse( $health_check->test(), 'JS concatenation should fail when not enabled.' );
	}

	/**
	 * Test CloudflareHealthCheck.
	 */
	public function test_cloudflare_health_check() {
		$_SERVER['HTTP_CF_RAY'] = 'some-value';

		$health_check = new CloudflareHealthCheck();
		$this->assertTrue( $health_check->test(), 'Cloudflare should pass when HTTP_CF_RAY is set.' );

		unset( $_SERVER['HTTP_CF_RAY'] );

		$this->assertFalse( $health_check->test(), 'Cloudflare should fail when HTTP_CF_RAY is not set.' );
	}

	/**
	 * Test PrioritizeCssHealthCheck.
	 */
	public function test_prioritize_css_health_check() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'jetpack_boost_status_critical-css', false )
			->once()
			->andReturn( true );

		$health_check = new PrioritizeCssHealthCheck();
		$this->assertTrue( $health_check->test(), 'Prioritizing critical CSS should pass when enabled.' );

		WP_Mock::userFunction( 'get_option' )
			->with( 'jetpack_boost_status_critical-css', false )
			->once()
			->andReturn( false );

		$this->assertFalse( $health_check->test(), 'Prioritizing critical CSS should fail when not enabled.' );
	}

	/**
	 * Test DeferNonEssentialJsHealthCheck.
	 */
	public function test_defer_non_essential_js_health_check() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'jetpack_boost_status_render-blocking-js', false )
			->once()
			->andReturn( true );

		$health_check = new DeferNonEssentialJsHealthCheck();
		$this->assertTrue( $health_check->test(), 'Deferring non-essential JS should pass when enabled.' );

		WP_Mock::userFunction( 'get_option' )
			->with( 'jetpack_boost_status_render-blocking-js', false )
			->once()
			->andReturn( false );

		$this->assertFalse( $health_check->test(), 'Deferring non-essential JS should fail when not enabled.' );
	}

	/**
	 * Test PersistentObjectCacheHealthCheck.
	 */
	public function test_persistent_object_cache_health_check() {
		WP_Mock::userFunction( 'wp_using_ext_object_cache' )
			->once()
			->andReturn( true );

		$health_check = new PersistentObjectCacheHealthCheck();
		$this->assertTrue( $health_check->test(), 'Persistent object caching should pass when enabled.' );

		WP_Mock::userFunction( 'wp_using_ext_object_cache' )
			->once()
			->andReturn( false );

		$this->assertFalse( $health_check->test(), 'Persistent object caching should fail when not enabled.' );
	}

	/**
	 * Test CronLockTimeoutHealthCheck.
	 */
	public function test_cron_lock_timeout_health_check() {
		Patchwork\redefine(
			'defined',
			function ( $constant_name ) {
				if ( 'WP_CRON_LOCK_TIMEOUT' === $constant_name ) {
					return true;
				}
				return Patchwork\relay();
			}
		);

		Patchwork\redefine(
			'constant',
			function ( $constant_name ) {
				if ( 'WP_CRON_LOCK_TIMEOUT' === $constant_name ) {
					return 60;
				}
				return Patchwork\relay();
			}
		);

		$health_check = new CronLockTimeoutHealthCheck();
		$this->assertTrue( $health_check->test(), 'Cron lock timeout should pass when set to 60 seconds or less.' );

		Patchwork\redefine(
			'constant',
			function ( $constant_name ) {
				if ( 'WP_CRON_LOCK_TIMEOUT' === $constant_name ) {
					return 600;
				}
				return Patchwork\relay();
			}
		);

		$this->assertFalse( $health_check->test(), 'Cron lock timeout should fail when set to more than 60 seconds.' );
	}
}
