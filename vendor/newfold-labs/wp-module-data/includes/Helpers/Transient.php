<?php
namespace NewfoldLabs\WP\Module\Data\Helpers;

/**
 * Custom Transient class to handle an Options API based fallback
 */
class Transient {

	/**
	 * Whether to use transients to store temporary data
	 *
	 * If the site has an object-cache.php drop-in, then we can't reliably
	 * use the transients API. We'll try to fall back to the options API.
	 */
	protected static function should_use_transients(): bool {
		require_once constant( 'ABSPATH' ) . '/wp-admin/includes/plugin.php';
		return ! array_key_exists( 'object-cache.php', get_dropins() )
			|| 'atomic' === \NewfoldLabs\WP\Context\getContext( 'platform' ); // Bluehost Cloud.
	}

	/**
	 * Wrapper for get_transient() with Options API fallback
	 *
	 * @see \get_transient()
	 * @see \get_option()
	 * @see \delete_option()
	 *
	 * @param string $key The key of the transient to retrieve
	 * @return mixed The value of the transient
	 */
	public static function get( string $key ) {
		if ( self::should_use_transients() ) {
			return \get_transient( $key );
		}

		/**
		 * Implement the filters as used in {@see get_transient()}.
		 */
		$pre = apply_filters( "pre_transient_{$key}", false, $key );
		if ( false !== $pre ) {
			return $pre;
		}

		/**
		 * The saved value and the Unix time it expires at.
		 *
		 * @var array{value:mixed, expires_at:int} $data
		 */
		$data = \get_option( $key );
		if ( is_array( $data ) && isset( $data['expires_at'], $data['value'] ) ) {
			if ( $data['expires_at'] > time() ) {
				$value = $data['value'];
			} else {
				\delete_option( $key );
				$value = false;
			}
		} else {
			/**
			 * Set $value to false if $data is not a valid array.
			 * This is to prevent PHP notices when trying to access $data['expires_at'].
			 */
			$value = false;
        }

		/**
		 * Implement the filters as used in {@see get_transient()}.
		 */
		return apply_filters( "transient_{$key}", $value, $key );
	}

	/**
	 * Wrapper for set_transient() with Options API fallback
	 *
	 * @see \set_transient()
	 * @see \update_option()
	 *
	 * @param string  $key        Key to use for storing the transient
	 * @param mixed   $value      Value to be saved
	 * @param integer $expires_in Optional expiration time in seconds from now. Default is 1 hour
	 *
	 * @return bool Whether the value was saved
	 */
	public static function set( string $key, $value, int $expires_in = 3600 ): bool {
		if ( self::should_use_transients() ) {
			return \set_transient( $key, $value, $expires_in );
		}

		/**
		 * Implement the filters as used in {@see set_transient()}.
		 */
		$value      = apply_filters( "pre_set_transient_{$key}", $value, $expires_in, $key );
		$expires_in = apply_filters( "expiration_of_transient_{$key}", $expires_in, $value, $key );

		$data = array(
			'value'      => $value,
			'expires_at' => $expires_in + time(),
		);

		$result = \update_option( $key, $data, false );

		if ( $result ) {
			do_action( "set_transient_{$key}", $value, $expires_in, $key );
			do_action( 'setted_transient', $key, $value, $expires_in );
		}

		return $result;
	}

	/**
	 * Wrapper for delete_transient() with Options API fallback
	 *
	 * @see \delete_transient()
	 * @see \delete_option()
	 *
	 * @param string $key The key of the transient/option to delete
	 * @return bool Whether the value was deleted
	 */
	public static function delete( $key ): bool {
		if ( self::should_use_transients() ) {
			return \delete_transient( $key );
		}

		/**
		 * Implement the filters as used in {@see set_transient()}.
		 *
		 * @param string $key Transient name.
		 */
		do_action( "delete_transient_{$key}", $key );

		$result = \delete_option( $key );

		if ( $result ) {

			/**
			 * Implement the filters as used in {@see set_transient()}.
			 *
			 * @param string $transient Deleted transient name.
			 */
			do_action( 'deleted_transient', $key );
		}

		return $result;
	}

	/**
	 * Make the static functions callable as instance methods.
	 *
	 * @param string $name The function name being called.
	 * @param array  $arguments The arguments passed to that function.
	 *
	 * @return mixed
	 * @throws \BadMethodCallException If the method does not exist.
	 */
	public function __call( $name, $arguments ) {
		if ( ! method_exists( __CLASS__, $name ) ) {
			throw new \BadMethodCallException( 'Method ' . esc_html( $name ) . ' does not exist' );
		}
		return self::$name( ...$arguments );
	}
}
