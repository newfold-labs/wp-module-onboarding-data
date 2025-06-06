<?php

namespace NewfoldLabs\WP\Module\Data\Helpers;

/**
 * Helper class for gathering and formatting Site Health data
 *
 * @since 1.7.0
 */
class SiteHealth {

	/**
	 * Raw Site health data.
	 *
	 * @since 1.7.0
	 *
	 * @var array
	 */
	private static $raw_debug_data;

	/**
	 * Safe Site Health data.
	 *
	 * All empty and private fields have been removed from self:$raw_debug_data.
	 *
	 * @since 1.7.0
	 *
	 * @var array
	 */
	private static $safe_debug_data;

	/**
	 * Retrieves a site's debug data through Site health.
	 *
	 * @since 1.7.0
	 *
	 * @return array The site's debug data.
	 */
	public static function get_raw_data() {
		if ( ! empty( self::$raw_debug_data ) ) {
			return self::$raw_debug_data;
		}

		require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/class-wp-debug-data.php' );

		self::$raw_debug_data = \WP_Debug_Data::debug_data();

		return self::$raw_debug_data;
	}

	/**
	 * Retrieves the debug data for a site that is safe for sharing.
	 *
	 * Any data marked `private` in Site Health (database user, for example) will not be included in this list.
	 *
	 * @since 1.7.0
	 *
	 * @return array List of Site Health debug data.
	 */
	public static function get_safe_data() {
		if ( ! empty( self::$safe_debug_data ) ) {
			return self::$safe_debug_data;
		}

		$safe_data = array();

		foreach ( self::get_raw_data() as $section => $details ) {
			// Skip this section if there are no fields, or the section has been declared as private.
			if ( empty( $details['fields'] ) || ( isset( $details['private'] ) && $details['private'] ) ) {
				continue;
			}

			foreach ( $details['fields'] as $field_name => $field ) {
				if ( isset( $field['private'] ) && true === $field['private'] ) {
					continue;
				}

				if ( isset( $field['debug'] ) ) {
					$debug_data = $field['debug'];
				} else {
					$debug_data = $field['value'];
				}

				// Can be array, one level deep only.
				if ( is_array( $debug_data ) ) {
					$value = array();

					foreach ( $debug_data as $sub_field_name => $sub_field_value ) {
						$value[ $sub_field_name ] = $sub_field_value;
					}
				} elseif ( is_bool( $debug_data ) ) {
					$value = $debug_data ? 'true' : 'false';
				} elseif ( empty( $debug_data ) && '0' !== $debug_data ) {
					$value = 'undefined';
				} else {
					$value = $debug_data;
				}

				$safe_data[ $section ][ $field_name ] = $value;
			}
		}

		self::$safe_debug_data = $safe_data;

		return self::$safe_debug_data;
	}

	/**
	 * Calculates the Site Health score for a site.
	 *
	 * The score is the number of successful tests (good) divided by the total number of tests.
	 *
	 * @since 1.7.0
	 *
	 * @param string $results A JSON encoded string of Site Health test results.
	 *                        This will usually be the value of the `health-check-site-status-result` transient
	 *                        in WordPress Core.
	 * @return int Site Health score.
	 */
	public static function calculate_score( $results ) {
		$results = json_decode( $results, true );

		$total_tests = array_reduce(
			$results,
			function ( $total, $item ) {
				return $total += (int) $item;
			}
		);

		// Report a -1 when there are no Site Health tests
		if ( 0 >= $total_tests ) {
			return -1;
		}

		return round( (int) $results['good'] / $total_tests * 100 );
	}
}
