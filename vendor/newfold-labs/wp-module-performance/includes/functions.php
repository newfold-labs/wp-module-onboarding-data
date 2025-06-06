<?php

namespace NewfoldLabs\WP\Module\Performance;

use NewfoldLabs\WP\Module\Performance\Skip404\Skip404;
use NewfoldLabs\WP\Module\Performance\Cache\CacheManager;
use NewfoldLabs\WP\Module\Performance\Cache\CacheExclusion;

/**
 * Return defaul exclusions.
 *
 * @return array
 */
function get_default_cache_exclusions() {
	return join( ',', array( 'cart', 'checkout', 'wp-admin', rest_get_url_prefix() ) );
}

/**
 * Get the current cache level.
 *
 * @return int Cache level.
 */
function get_cache_level() {
	return absint( get_option( CacheManager::OPTION_CACHE_LEVEL, 2 ) );
}

/**
 * Get the cache exclusion.
 *
 * @return int Cache exclusion.
 */
function get_cache_exclusion() {
	return get_option( CacheExclusion::OPTION_CACHE_EXCLUSION, get_default_cache_exclusions() );
}

/**
 * Get the "Skip WordPress 404 Handling for Static Files" option.
 *
 * @return bool Whether to skip 404 handling for static files.
 */
function get_skip404_option() {
	return (bool) get_option( Skip404::OPTION_NAME, true );
}

/**
 * Check if page caching is enabled.
 *
 * @return bool
 */
function should_cache_pages() {
	return get_cache_level() > 1;
}

/**
 * Check if asset caching is enabled.
 *
 * @return bool
 */
function should_cache_assets() {
	return get_cache_level() > 0;
}

/**
 * Remove a directory.
 *
 * @param string $path Path to the directory.
 */
function remove_directory( $path ) {
	global $wp_filesystem;

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	WP_Filesystem();

	if ( ! $wp_filesystem || ! $wp_filesystem->is_dir( $path ) ) {
		return;
	}

	$files = $wp_filesystem->dirlist( $path );

	foreach ( $files as $file => $file_info ) {
		$file_path = trailingslashit( $path ) . $file;
		if ( 'f' === $file_info['type'] ) {
			$wp_filesystem->delete( $file_path );
		} elseif ( 'd' === $file_info['type'] ) {
			remove_directory( $file_path );
		}
	}

	$wp_filesystem->rmdir( $path );
}

/**
 * Convert a string to snake case.
 *
 * @param string $value     String to be converted.
 * @param string $delimiter Delimiter (can be a dash for conversion to kebab case).
 *
 * @return string
 */
function to_snake_case( string $value, string $delimiter = '_' ) {
	if ( ! ctype_lower( $value ) ) {
		$value = preg_replace( '/(\s+)/u', '', ucwords( $value ) );
		$value = trim( mb_strtolower( preg_replace( '/([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)/u', '$1' . $delimiter, $value ), 'UTF-8' ), $delimiter );
	}

	return $value;
}

/**
 * Convert a string to studly case.
 *
 * @param string $value String to be converted.
 *
 * @return string
 */
function to_studly_case( $value ) {
	return str_replace( ' ', '', ucwords( str_replace( array( '-', '_' ), ' ', $value ) ) );
}

/**
 * Get styles path.
 *
 * return string
 */
function get_styles_path() {
	return 'vendor/newfold-labs/wp-module-performance/styles/styles.css';
}

/**
 * Get js script path.
 *
 * @param string $script_name Script name.
 * return string
 */
function get_scripts_path( $script_name = '' ) {
	$basePath = 'vendor/newfold-labs/wp-module-performance/scripts/';
	if ( empty( $script_name ) ) {
		return $basePath;
	}
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	return "vendor/newfold-labs/wp-module-performance/scripts/$script_name$suffix.js";
}

/**
 * Detect if the current page is a Brand Plugin settings page.
 *
 * @param string $brand The expected settings page identifier.
 * @return boolean True if the current page matches the brand settings page, false otherwise.
 */
function is_settings_page( $brand ) {

	$current_url = ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http' ) .
	'://' .
	( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' ) .
	( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' );

	$parsed_url = wp_parse_url( $current_url );

	if ( ! isset( $parsed_url['query'] ) ) {
		return false;
	}

	parse_str( $parsed_url['query'], $query_params );

	if ( ! isset( $query_params['page'] ) || $query_params['page'] !== $brand ) {
		return false;
	}

	return true;
}
