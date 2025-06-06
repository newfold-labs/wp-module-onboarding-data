<?php

namespace WP_Forge\WP_Htaccess_Manager;

/**
 * Get the default .htaccess file.
 *
 * @return string
 */
function getDefaultHtaccessFile() {
	// Ensure get_home_path() is declared.
	require_once ABSPATH . 'wp-admin/includes/file.php';

	return get_home_path() . '.htaccess';
}

/**
 * Get the opening file marker.
 *
 * @param string $marker The marker name.
 *
 * @return string
 */
function getOpeningMarker( $marker ) {
	return "# BEGIN {$marker}";
}

/**
 * Get the closing file marker.
 *
 * @param string $marker The marker name.
 *
 * @return string
 */
function getClosingMarker( $marker ) {
	return "# END {$marker}";
}

/**
 * Check if a file has a specific marker.
 *
 * @param string      $marker The marker name.
 * @param string|null $file   The path to the file.
 *
 * @return bool
 */
function hasMarkers( $marker, $file = null ) {
	$fs   = filesystem();
	$file = $file ?? getDefaultHtaccessFile();

	if ( ! $fs->is_file( $file ) || ! $fs->is_readable( $file ) ) {
		return false;
	}

	$content = $fs->get_contents( $file );

	return $content &&
			false !== strpos( $content, getOpeningMarker( $marker ) ) &&
			false !== strpos( $content, getClosingMarker( $marker ) );
}

/**
 * Add markers to file.
 *
 * Note: Be sure to check if hasMarkers() returns true first!
 *
 * @param string      $marker   The marker name.
 * @param string      $location The location to insert markers (e.g. before or after).
 * @param string|null $file     The path to the file.
 *
 * @return bool
 */
function addMarkers( $marker, $location = 'before', $file = null ) {
	$fs   = filesystem();
	$file = $file ?? getDefaultHtaccessFile();
	if ( ! $fs->is_file( $file ) || ! $fs->is_readable( $file ) || ! $fs->is_writable( $file ) ) {
		return false;
	}
	$lines = convertContentToLines( $fs->get_contents( $file ) );
	if ( empty( $lines ) ) {
		return false;
	}
	$lines = getLinesRelativeToMarkers( $lines, $marker );

	switch ( $location ) {
		case 'after':
			$content = implode(
				PHP_EOL,
				array_merge(
					$lines['beforeMarkers'],
					$lines['afterMarkers'],
					array(
						getOpeningMarker( $marker ),
						getClosingMarker( $marker ),
					)
				)
			);
			break;
		default:
			$content = implode(
				PHP_EOL,
				array_merge(
					array(
						getOpeningMarker( $marker ),
						getClosingMarker( $marker ),
					),
					$lines['beforeMarkers'],
					$lines['afterMarkers']
				)
			);
	}

	return $fs->put_contents( $file, $content, 0644 );
}

/**
 * Remove markers from a file.
 *
 * @param string      $marker The marker name.
 * @param string|null $file   The path to the file.
 *
 * @return bool
 */
function removeMarkers( $marker, $file = null ) {
	$fs   = filesystem();
	$file = $file ?? getDefaultHtaccessFile();
	if ( ! $fs->is_file( $file ) || ! $fs->is_readable( $file ) || ! $fs->is_writable( $file ) ) {
		return false;
	}
	$lines = convertContentToLines( $fs->get_contents( $file ) );
	if ( empty( $lines ) ) {
		return false;
	}
	$lines   = getLinesRelativeToMarkers( $lines, $marker );
	$content = implode(
		PHP_EOL,
		array_merge(
			$lines['beforeMarkers'],
			$lines['afterMarkers']
		)
	);

	return $fs->put_contents( $file, $content, 0644 );
}

/**
 * Extract content from file markers.
 *
 * @param string      $marker The marker name.
 * @param string|null $file   The path to the file.
 *
 * @return string
 */
function extractContent( $marker, $file = null ) {
	$fs   = filesystem();
	$file = $file ?? getDefaultHtaccessFile();
	if ( ! $fs->is_file( $file ) || ! $fs->is_readable( $file ) ) {
		return '';
	}
	$lines = convertContentToLines( $fs->get_contents( $file ) );
	if ( empty( $lines ) ) {
		return '';
	}
	$lines = getLinesRelativeToMarkers( $lines, $marker );

	return implode( PHP_EOL, $lines['betweenMarkers'] );
}

/**
 * Insert content between file markers.
 *
 * @param string       $marker  The marker name.
 * @param string|array $content The content of the file as a string or array of lines.
 * @param string|null  $file    The path to the file.
 *
 * @return bool
 */
function insertContent( $marker, $content, $file = null ) {
	$fs   = filesystem();
	$file = $file ?? getDefaultHtaccessFile();
	if ( ! $fs->is_file( $file ) || ! $fs->is_readable( $file ) || ! $fs->is_writable( $file ) ) {
		return false;
	}
	$lines = convertContentToLines( $fs->get_contents( $file ) );
	if ( empty( $lines ) ) {
		return false;
	}
	$lines = getLinesRelativeToMarkers( $lines, $marker );

	$lines['betweenMarkers'] = convertContentToLines( $content );

	$content = implode(
		PHP_EOL,
		array_merge(
			$lines['beforeMarkers'],
			array( getOpeningMarker( $marker ) ),
			$lines['betweenMarkers'],
			array( getClosingMarker( $marker ) ),
			$lines['afterMarkers']
		)
	);

	return $fs->put_contents( $file, $content, 0644 );
}

/**
 * Add content to htaccess file.
 * 
 * @param string       $marker   The marker name.
 * @param string|array $content  The content of the file as a string or array of lines.
 * @param string       $location The location to insert markers (e.g. before or after).
 * @param string       $file     The file path.
 *
 * @return bool
 */
function addContent( $marker, $content, $location = 'before', $file = null ) {
	if ( hasMarkers( $marker, $file ) ) {
		return insertContent( $marker, $content, $file );
	}
	if ( addMarkers( $marker, $location, $file ) ) {
		return insertContent( $marker, $content, $file );
	}

	return false;
}

/**
 * Get lines relative to markers.
 *
 * @param array  $lines  An array of file lines.
 * @param string $marker The marker name.
 *
 * @return array[]
 */
function getLinesRelativeToMarkers( array $lines, $marker ) {

	$openingMarker = getOpeningMarker( $marker );
	$closingMarker = getClosingMarker( $marker );

	$foundOpeningMarker = false;
	$foundClosingMarker = false;

	$beforeMarkers  = array();
	$betweenMarkers = array();
	$afterMarkers   = array();

	foreach ( $lines as $line ) {
		if ( ! $foundOpeningMarker && false !== strpos( $line, $openingMarker ) ) {
			$foundOpeningMarker = true;
			continue;
		} elseif ( ! $foundClosingMarker && false !== strpos( $line, $closingMarker ) ) {
			$foundClosingMarker = true;
			continue;
		}
		if ( ! $foundOpeningMarker ) {
			$beforeMarkers[] = $line;
		} elseif ( $foundOpeningMarker && $foundClosingMarker ) {
			$afterMarkers[] = $line;
		} else {
			$betweenMarkers[] = $line;
		}
	}

	return array(
		'beforeMarkers'  => $beforeMarkers,
		'betweenMarkers' => $betweenMarkers,
		'afterMarkers'   => $afterMarkers,
	);
}

/**
 * Convert the contents of a file to an array of lines.
 *
 * @param string $content File content.
 *
 * @return array
 */
function convertContentToLines( $content ) {
	$lines = array();

	if ( is_array( $content ) ) {
		return $content;
	}

	if ( is_string( $content ) ) {
		if ( ! mb_check_encoding( $content, 'UTF-8' ) ) {
			$content = mb_convert_encoding( $content, 'UTF-8', 'auto' );
		}
		$lines = preg_split( '/\r\n|\r|\n/', $content );
		$lines = array_filter( $lines, function( $line ) { return trim( $line ) !== ''; } );
	}

	return is_array( $lines ) ? (array) $lines : array();
}

/**
 * Get WordPress filesystem instance.
 *
 * @return \WP_Filesystem_Direct
 */
function filesystem() {
	static $filesystem;

	if ( ! isset( $filesystem ) ) {

		if ( ! class_exists( 'WP_Filesystem_Base' ) ) {
			require ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		}

		if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
			require ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
		}

		$filesystem = new \WP_Filesystem_Direct( false );
	}

	return $filesystem;
}
