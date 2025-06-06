<?php

namespace NewfoldLabs\WP\Module\Performance\Cache\Types\WPCLI;

use NewfoldLabs\WP\Module\Performance\Cache\CacheExclusion;
use NewfoldLabs\WP\Module\Performance\Cache\CacheManager;
use NewfoldLabs\WP\Module\Performance\NFD_WPCLI;
use NewfoldLabs\WP\Module\Performance\Skip404\Skip404;

/**
 * Handles WP-CLI commands for Cache settings.
 */
class CacheTypesCommandHandler {

	/**
	 * Validates cache level.
	 *
	 * @param mixed $level The cache level value.
	 *
	 * @return int The validated cache level.
	 */
	private function validate_cache_level( $level ) {
		if ( ! is_numeric( $level ) || ! in_array( (int) $level, CacheManager::VALID_CACHE_LEVELS, true ) ) {
			NFD_WPCLI::error(
				sprintf(
					/* translators: %s is the list of valid cache levels. */
					__( 'Invalid cache level. Use one of the following: %s.', 'wp-module-performance' ),
					implode( ', ', CacheManager::VALID_CACHE_LEVELS )
				)
			);
		}
		return (int) $level;
	}

	/**
	 * Toggles the newfold_cache_level setting.
	 *
	 * ## OPTIONS
	 *
	 * <level>
	 * : Set cache level (0, 1, 2, or 3).
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance cache level 2
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function level( $args ) {
		if ( ! isset( $args[0] ) ) {
			NFD_WPCLI::error(
				sprintf(
					/* translators: %s is the list of valid cache levels. */
					__( 'A value for cache level is required. Use one of the following: %s.', 'wp-module-performance' ),
					implode( ', ', CacheManager::VALID_CACHE_LEVELS )
				)
			);
		}
		$level = $this->validate_cache_level( $args[0] );
		update_option( CacheManager::OPTION_CACHE_LEVEL, $level );
		NFD_WPCLI::success(
			sprintf(
				/* translators: %s is the new cache level. */
				__( "Setting 'newfold_cache_level' has been set to '%s'.", 'wp-module-performance' ),
				$level
			)
		);
	}

	/**
	 * Toggles the newfold_skip_404_handling setting.
	 *
	 * ## OPTIONS
	 *
	 * <status>
	 * : Enable or disable skipping 404 handling. Accepts 'true' or 'false'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance cache skip_404 true
	 *     wp nfd performance cache skip_404 false
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function skip_404( $args ) {
		if ( ! isset( $args[0] ) || ! in_array( strtolower( $args[0] ), array( 'true', 'false' ), true ) ) {
			NFD_WPCLI::error( __( "Invalid value. Use 'true' or 'false' for skip_404 handling.", 'wp-module-performance' ) );
		}
		$status = filter_var( $args[0], FILTER_VALIDATE_BOOLEAN );
		update_option( Skip404::OPTION_NAME, $status );
		NFD_WPCLI::success(
			sprintf(
				/* translators: %s is the new boolean status. */
				__( "Setting 'newfold_skip_404_handling' has been set to '%s'.", 'wp-module-performance' ),
				$status ? 'true' : 'false'
			)
		);
	}

	/**
	 * Updates the cache exclusion list.
	 *
	 * ## OPTIONS
	 *
	 * <exclusions>
	 * : Comma-separated list of URLs or patterns to exclude from caching.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance cache exclude "checkout,cart,my-account"
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function exclude( $args ) {
		if ( ! isset( $args[0] ) ) {
			NFD_WPCLI::error( __( 'A value for cache exclusion is required.', 'wp-module-performance' ) );
		}

		// Sanitize the entire input
		$value = sanitize_text_field( $args[0] );

		// Sanitize each keyword since it's a comma-separated list
		$exclusions      = array_map( 'trim', explode( ',', $value ) );
		$sanitized_value = implode( ',', $exclusions );

		update_option( CacheExclusion::OPTION_CACHE_EXCLUSION, $sanitized_value );
		NFD_WPCLI::success(
			sprintf(
			/* translators: %s is the new cache exclusion list. */
				__( "Setting 'newfold_cache_exclusion' has been updated to: '%s'.", 'wp-module-performance' ),
				$sanitized_value
			)
		);
	}
}
