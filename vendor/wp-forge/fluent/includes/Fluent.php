<?php

namespace WP_Forge\Fluent;

use ArrayAccess;
use JsonSerializable;

class Fluent implements ArrayAccess, JsonSerializable {

	/**
	 * All of the attributes set on the fluent instance.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Create a new fluent instance.
	 *
	 * @param iterable $attributes
	 *
	 * @return void
	 */
	public function __construct( $attributes = [] ) {
		foreach ( $attributes as $key => $value ) {
			$this->attributes[ $key ] = $value;
		}
	}

	/**
	 * Get an attribute from the fluent instance.
	 *
	 * @param string|int $key
	 * @param mixed      $default
	 *
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		if ( array_key_exists( $key, $this->attributes ) ) {
			return $this->attributes[ $key ];
		}

		return $default;
	}

	/**
	 * Set an attribute on the fluent instance.
	 *
	 * @param string|int $key
	 * @param mixed      $value
	 *
	 * @return $this
	 */
	public function set( $key, $value ) {
		$this->attributes[ $key ] = $value;

		return $this;
	}

	/**
	 * Check if an attribute exists on the fluent instance.
	 *
	 * @param string|int $key
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return array_key_exists( $key, $this->attributes );
	}

	/**
	 * Remove an attribute from the fluent instance.
	 *
	 * @param string|int $key
	 *
	 * @return $this
	 */
	public function delete( $key ) {
		unset( $this->attributes[ $key ] );

		return $this;
	}

	/**
	 * Convert the fluent instance to an array.
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->attributes;
	}

	/**
	 * Convert the object into something JSON serializable.
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * Convert the fluent instance to JSON.
	 *
	 * @param int $options
	 *
	 * @return string
	 */
	public function toJson( $options = 0 ) {
		return json_encode( $this->jsonSerialize(), $options );
	}

	/**
	 * Determine if the given offset exists.
	 *
	 * @param string|int $offset
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset( $this->attributes[ $offset ] );
	}

	/**
	 * Get the value for a given offset.
	 *
	 * @param string|int $offset
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->get( $offset );
	}

	/**
	 * Set the value at the given offset.
	 *
	 * @param string|int $offset
	 * @param mixed      $value
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		$this->attributes[ $offset ] = $value;
	}

	/**
	 * Unset the value at the given offset.
	 *
	 * @param string|int $offset
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		unset( $this->attributes[ $offset ] );
	}

	/**
	 * Handle dynamic calls to the fluent instance to set attributes.
	 *
	 * @param string $method
	 * @param array  $parameters
	 *
	 * @return $this
	 */
	public function __call( $method, $parameters ) {
		$this->attributes[ $method ] = count( $parameters ) > 0 ? $parameters[0] : true;

		return $this;
	}

	/**
	 * Dynamically retrieve the value of an attribute.
	 *
	 * @param string|int $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * Dynamically set the value of an attribute.
	 *
	 * @param string|int $key
	 * @param mixed      $value
	 *
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	/**
	 * Dynamically check if an attribute is set.
	 *
	 * @param string|int $key
	 *
	 * @return bool
	 */
	public function __isset( $key ) {
		return $this->offsetExists( $key );
	}

	/**
	 * Dynamically unset an attribute.
	 *
	 * @param string|int $key
	 *
	 * @return void
	 */
	public function __unset( $key ) {
		$this->offsetUnset( $key );
	}
}
