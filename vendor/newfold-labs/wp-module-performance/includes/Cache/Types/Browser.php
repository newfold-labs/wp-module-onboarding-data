<?php

namespace NewfoldLabs\WP\Module\Performance\Cache\Types;

use NewfoldLabs\WP\Module\Performance\OptionListener;
use NewfoldLabs\WP\ModuleLoader\Container;
use WP_Forge\WP_Htaccess_Manager\htaccess;
use NewfoldLabs\WP\Module\Performance\Cache\CacheExclusion;
use NewfoldLabs\WP\Module\Performance\Cache\CacheManager;

use function NewfoldLabs\WP\Module\Performance\get_cache_level;
use function WP_Forge\WP_Htaccess_Manager\removeMarkers;

/**
 * Browser cache type.
 */
class Browser extends CacheBase {
	/**
	 * The file marker name.
	 *
	 * @var string
	 */
	const MARKER = 'Newfold Browser Cache';

	/**
	 * Whether or not the code for this cache type should be loaded.
	 *
	 * @param Container $container Dependency injection container.
	 *
	 * @return bool
	 */
	public static function should_enable( Container $container ) {
		return (bool) $container->has( 'isApache' ) && $container->get( 'isApache' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		new OptionListener( CacheManager::OPTION_CACHE_LEVEL, array( __CLASS__, 'maybeAddRules' ) );

		new OptionListener( CacheExclusion::OPTION_CACHE_EXCLUSION, array( __CLASS__, 'exclusionChange' ) );

		add_filter( 'newfold_update_htaccess', array( $this, 'on_rewrite' ) );
	}

	/**
	 * When updating .htaccess, also update our rules as appropriate.
	 */
	public function on_rewrite() {
		self::maybeAddRules( get_cache_level() );
	}

	/**
	 * Manage on exlcusion option change.
	 */
	public static function exclusionChange() {
		self::maybeAddRules( get_cache_level() );
	}

	/**
	 * Determine whether to add or remove rules based on caching level.
	 *
	 * @param int|null $cacheLevel The caching level.
	 */
	public static function maybeAddRules( $cacheLevel ) {
		absint( $cacheLevel ) > 0 ? self::addRules( $cacheLevel ) : self::removeRules();
	}

	/**
	 * Remove our rules from the .htaccess file.
	 */
	public static function removeRules() {
		removeMarkers( self::MARKER );
	}

	/**
	 * Add our rules to the .htaccess file.
	 *
	 * @param int $cacheLevel The caching level.
	 *
	 * @return bool
	 */
	public static function addRules( $cacheLevel ) {

		$fileTypeExpirations = self::getFileTypeExpirations( $cacheLevel );

		$tab = "\t";

		$rules[] = '<IfModule mod_expires.c>';
		$rules[] = "{$tab}ExpiresActive On";

		foreach ( $fileTypeExpirations as $file_type => $expiration ) {
			if ( 'default' === $file_type ) {
				$rules[] = "{$tab}ExpiresDefault \"access plus {$expiration}\"";
			} else {
				$rules[] = "{$tab}ExpiresByType {$file_type} \"access plus {$expiration}\"";
			}
		}
		$rules[] = '</IfModule>';

		$cache_exclusion = get_option( CacheExclusion::OPTION_CACHE_EXCLUSION, '' );
		if ( is_string( $cache_exclusion ) && '' !== $cache_exclusion ) {
			$cache_exclusion_parameters = array_map( 'trim', explode( ',', sanitize_text_field( get_option( CacheExclusion::OPTION_CACHE_EXCLUSION, '' ) ) ) );
			$cache_exclusion_parameters = implode( '|', $cache_exclusion_parameters );

			// Add the cache exclusion rules.
			$rules[] = '<IfModule mod_rewrite.c>';
			$rules[] = 'RewriteEngine On';
			$rules[] = "RewriteCond %{REQUEST_URI} ^/({$cache_exclusion_parameters}) [NC]";
			$rules[] = '<IfModule mod_headers.c>';
			$rules[] = 'Header set Cache-Control "no-cache, no-store, must-revalidate"';
			$rules[] = 'Header set Pragma "no-cache"';
			$rules[] = 'Header set Expires 0';
			$rules[] = '</IfModule>';
			$rules[] = '</IfModule>';
			// Add the end of the rules about cache exclusion.
		}

		$htaccess = new htaccess( self::MARKER );

		return $htaccess->addContent( $rules );
	}

	/**
	 * Get the filetype expirations based on the current caching level.
	 *
	 * @param int $cacheLevel The caching level.
	 *
	 * @return string[]
	 */
	protected static function getFileTypeExpirations( int $cacheLevel ) {

		switch ( $cacheLevel ) {
			case 3:
				return array(
					'default'         => '1 week',
					'text/html'       => '8 hours',
					'image/jpg'       => '1 week',
					'image/jpeg'      => '1 week',
					'image/gif'       => '1 week',
					'image/png'       => '1 week',
					'text/css'        => '1 week',
					'text/javascript' => '1 week',
					'application/pdf' => '1 month',
					'image/x-icon'    => '1 year',
				);

			case 2:
				return array(
					'default'         => '24 hours',
					'text/html'       => '2 hours',
					'image/jpg'       => '24 hours',
					'image/jpeg'      => '24 hours',
					'image/gif'       => '24 hours',
					'image/png'       => '24 hours',
					'text/css'        => '24 hours',
					'text/javascript' => '24 hours',
					'application/pdf' => '1 week',
					'image/x-icon'    => '1 year',
				);

			case 1:
				return array(
					'default'         => '5 minutes',
					'text/html'       => '0 seconds',
					'image/jpg'       => '1 hour',
					'image/jpeg'      => '1 hour',
					'image/gif'       => '1 hour',
					'image/png'       => '1 hour',
					'text/css'        => '1 hour',
					'text/javascript' => '1 hour',
					'application/pdf' => '6 hours',
					'image/x-icon'    => '1 year',
				);

			default:
				return array();
		}
	}

	/**
	 * Handle activation logic.
	 */
	public static function on_activation() {
		self::maybeAddRules( get_cache_level() );
	}

	/**
	 * Handle deactivation logic.
	 */
	public static function on_deactivation() {
		self::removeRules();
	}
}
