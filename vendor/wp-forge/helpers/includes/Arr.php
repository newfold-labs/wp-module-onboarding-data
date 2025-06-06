<?php

namespace WP_Forge\Helpers;

use ArrayAccess;

/**
 * Class Arr
 *
 * @package WP_Forge\Helpers
 */
class Arr {

	/**
	 * Determine whether the given value is accessible.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function accessible( $value ) {
		return is_array( $value ) || $value instanceof ArrayAccess;
	}

	/**
	 * Add an element to an array using "dot" notation if it doesn't exist.
	 *
	 * @param array  $array
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return array
	 */
	public static function add( $array, $key, $value ) {
		if ( is_null( static::get( $array, $key ) ) ) {
			static::set( $array, $key, $value );
		}

		return $array;
	}

	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param array|ArrayAccess $array
	 * @param string|int|null   $key
	 * @param mixed             $default
	 *
	 * @return mixed
	 */
	public static function get( $array, $key, $default = null ) {
		if ( ! static::accessible( $array ) ) {
			return $default;
		}

		if ( is_null( $key ) ) {
			return $array;
		}

		if ( static::exists( $array, $key ) ) {
			return $array[ $key ];
		}

		if ( strpos( $key, '.' ) === false ) {
			return isset( $array[ $key ] ) ? $array[ $key ] : $default;
		}

		foreach ( explode( '.', $key ) as $segment ) {
			if ( static::accessible( $array ) && static::exists( $array, $segment ) ) {
				$array = $array[ $segment ];
			} else {
				return $default;
			}
		}

		return $array;
	}

	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param array      $array
	 * @param string|int $key
	 *
	 * @return bool
	 */
	public static function exists( $array, $key ) {
		if ( $array instanceof ArrayAccess ) {
			return $array->offsetExists( $key );
		}

		return array_key_exists( $key, $array );
	}

	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param array       $array
	 * @param string|null $key
	 * @param mixed       $value
	 *
	 * @return array
	 */
	public static function set( &$array, $key, $value ) {
		if ( is_null( $key ) ) {
			return $array = $value;
		}

		$keys = explode( '.', $key );

		while ( count( $keys ) > 1 ) {
			$key = array_shift( $keys );

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if ( ! isset( $array[ $key ] ) || ! is_array( $array[ $key ] ) ) {
				$array[ $key ] = [];
			}

			$array = &$array[ $key ];
		}

		$array[ array_shift( $keys ) ] = $value;

		return $array;
	}

	/**
	 * Get all of the given array except for a specified array of keys.
	 *
	 * @param array        $array
	 * @param array|string $keys
	 *
	 * @return array
	 */
	public static function except( $array, $keys ) {
		static::forget( $array, $keys );

		return $array;
	}

	/**
	 * Remove one or many array items from a given array using "dot" notation.
	 *
	 * @param array        $array
	 * @param array|string $keys
	 *
	 * @return void
	 */
	public static function forget( &$array, $keys ) {
		$original = &$array;

		$keys = (array) $keys;

		if ( count( $keys ) === 0 ) {
			return;
		}

		foreach ( $keys as $key ) {
			// if the exact key exists in the top-level, remove it
			if ( static::exists( $array, $key ) ) {
				unset( $array[ $key ] );

				continue;
			}

			$parts = explode( '.', $key );

			// clean up before each pass
			$array = &$original;

			while ( count( $parts ) > 1 ) {
				$part = array_shift( $parts );

				if ( isset( $array[ $part ] ) && is_array( $array[ $part ] ) ) {
					$array = &$array[ $part ];
				} else {
					continue 2;
				}
			}

			unset( $array[ array_shift( $parts ) ] );
		}
	}

	/**
	 * Return the last element in an array passing a given truth test.
	 *
	 * @param array         $array
	 * @param callable|null $callback
	 * @param mixed         $default
	 *
	 * @return mixed
	 */
	public static function last( $array, callable $callback = null, $default = null ) {
		if ( is_null( $callback ) ) {
			return empty( $array ) ? $default : end( $array );
		}

		return static::first( array_reverse( $array, true ), $callback, $default );
	}

	/**
	 * Return the first element in an array passing a given truth test.
	 *
	 * @param iterable      $array
	 * @param callable|null $callback
	 * @param mixed         $default
	 *
	 * @return mixed
	 */
	public static function first( $array, callable $callback = null, $default = null ) {
		if ( is_null( $callback ) ) {
			if ( empty( $array ) ) {
				return $default;
			}

			foreach ( $array as $item ) {
				return $item;
			}
		}

		foreach ( $array as $key => $value ) {
			if ( $callback( $value, $key ) ) {
				return $value;
			}
		}

		return $default;
	}

	/**
	 * Check if an item or items exist in an array using "dot" notation.
	 *
	 * @param \ArrayAccess|array $array
	 * @param string|array       $keys
	 *
	 * @return bool
	 */
	public static function has( $array, $keys ) {
		$keys = (array) $keys;

		if ( ! $array || $keys === [] ) {
			return false;
		}

		foreach ( $keys as $key ) {
			$subKeyArray = $array;

			if ( static::exists( $array, $key ) ) {
				continue;
			}

			foreach ( explode( '.', $key ) as $segment ) {
				if ( static::accessible( $subKeyArray ) && static::exists( $subKeyArray, $segment ) ) {
					$subKeyArray = $subKeyArray[ $segment ];
				} else {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Determines if an array is associative.
	 *
	 * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	public static function isAssoc( array $array ) {
		$keys = array_keys( $array );

		return array_keys( $keys ) !== $keys;
	}

	/**
	 * Get a subset of the items from the given array.
	 *
	 * @param array        $array
	 * @param array|string $keys
	 *
	 * @return array
	 */
	public static function only( $array, $keys ) {
		return array_intersect_key( $array, array_flip( (array) $keys ) );
	}

	/**
	 * Pluck an array of values from an array.
	 *
	 * @param iterable          $array
	 * @param string|array      $value
	 * @param string|array|null $key
	 *
	 * @return array
	 */
	public static function pluck( $array, $value, $key = null ) {
		$results = [];

		list( $value, $key ) = static::explodePluckParameters( $value, $key );

		foreach ( $array as $item ) {
			$itemValue = self::get( $item, $value );

			// If the key is "null", we will just append the value to the array and keep
			// looping. Otherwise we will key the array using the value of the key we
			// received from the developer. Then we'll return the final array form.
			if ( is_null( $key ) ) {
				$results[] = $itemValue;
			} else {
				$itemKey = self::get( $item, $key );

				if ( is_object( $itemKey ) && method_exists( $itemKey, '__toString' ) ) {
					$itemKey = (string) $itemKey;
				}

				$results[ $itemKey ] = $itemValue;
			}
		}

		return $results;
	}

	/**
	 * Convert the array into a query string.
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	public static function query( $array ) {
		return http_build_query( $array, null, '&', PHP_QUERY_RFC3986 );
	}

	/**
	 * Filter the array using the given callback.
	 *
	 * @param array    $array
	 * @param callable $callback
	 *
	 * @return array
	 */
	public static function where( $array, callable $callback ) {
		return array_filter( $array, $callback, ARRAY_FILTER_USE_BOTH );
	}

	/**
	 * If the given value is not an array and not null, wrap it in one.
	 *
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function wrap( $value ) {
		if ( is_null( $value ) ) {
			return [];
		}

		return is_array( $value ) ? $value : [ $value ];
	}

	/**
	 * Explode the "value" and "key" arguments passed to "pluck".
	 *
	 * @param string|array      $value
	 * @param string|array|null $key
	 *
	 * @return array
	 */
	protected static function explodePluckParameters( $value, $key ) {
		$value = is_string( $value ) ? explode( '.', $value ) : $value;

		$key = is_null( $key ) || is_array( $key ) ? $key : explode( '.', $key );

		return [ $value, $key ];
	}


}
