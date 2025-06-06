<?php

namespace WP_Forge\Helpers;

/**
 * Get an item from an array or object using dot notation.
 *
 * @param mixed        $data
 * @param string|array $key
 * @param mixed        $default
 *
 * @return mixed
 */
function dataGet( $data, $key, $default = null ) {
	$segments = is_array( $key ) ? $key : explode( '.', $key );
	foreach ( $segments as $segment ) {
		if ( is_null( $segment ) ) {
			return $default;
		}
		if ( Arr::accessible( $data ) && Arr::exists( $data, $segment ) ) {
			$data = $data[ $segment ];
		} elseif ( is_object( $data ) && isset( $data->{$segment} ) ) {
			$data = $data->{$segment};
		} else {
			return $default;
		}
	}

	return $data;
}

/**
 * Set an item on an array or object using dot notation.
 *
 * @param mixed        $target
 * @param string|array $key
 * @param mixed        $value
 * @param bool         $overwrite
 *
 * @return mixed
 */
function dataSet( &$target, $key, $value, $overwrite = true ) {
	$segments = is_array( $key ) ? $key : explode( '.', $key );
	$segment  = array_shift( $segments );

	if ( Arr::accessible( $target ) ) {
		if ( $segments ) {
			if ( ! Arr::exists( $target, $segment ) ) {
				$target[ $segment ] = [];
			}

			dataSet( $target[ $segment ], $segments, $value, $overwrite );
		} elseif ( $overwrite || ! Arr::exists( $target, $segment ) ) {
			$target[ $segment ] = $value;
		}
	} elseif ( is_object( $target ) ) {
		if ( $segments ) {
			if ( ! isset( $target->{$segment} ) ) {
				$target->{$segment} = [];
			}

			dataSet( $target->{$segment}, $segments, $value, $overwrite );
		} elseif ( $overwrite || ! isset( $target->{$segment} ) ) {
			$target->{$segment} = $value;
		}
	} else {
		$target = [];

		if ( $segments ) {
			dataSet( $target[ $segment ], $segments, $value, $overwrite );
		} elseif ( $overwrite ) {
			$target[ $segment ] = $value;
		}
	}

	return $target;
}
