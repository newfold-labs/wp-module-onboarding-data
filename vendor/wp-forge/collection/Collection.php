<?php
/**
 * A PHP utility class for manipulating collections.
 */

namespace WP_Forge\Collection;

/**
 * Class Collection
 */
class Collection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

	/**
	 * The items contained in the collection.
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * Collection constructor.
	 *
	 * @param array $items Initial collection items
	 */
	public function __construct( array $items = [] ) {
		$this->items = $items;
	}

	/**
	 * Static method for creating a collection.
	 *
	 * @param array $items Initial collection items
	 *
	 * @return static
	 */
	public static function make( array $items = [] ) {
		return new static( $items );
	}

	/**
	 * Get all of the items in the collection.
	 *
	 * @return array
	 */
	public function all() {
		return $this->items;
	}

	/**
	 * Push all of the given items onto the collection.
	 *
	 * @param array $items Items to add to the collection
	 *
	 * @return $this
	 */
	public function concat( array $items ) {
		$collection = new static( $this->all() );
		foreach ( $items as $item ) {
			$collection->push( $item );
		}

		return $collection;
	}

	/**
	 * Determine if an item exists in the collection.
	 *
	 * @param mixed $key Item for which to check
	 *
	 * @param bool  $strict Whether or not to use a strict comparison
	 *
	 * @return bool
	 */
	public function contains( $key, $strict = true ) {
		return in_array( $key, $this->items, $strict );
	}

	/**
	 * Get the total number of items in the collection.
	 *
	 * @return int
	 */
	#[\ReturnTypeWillChange]
	public function count() {
		return count( $this->items );
	}

	/**
	 * Get the items in the collection that are not present in the given items.
	 *
	 * @param  array $items Items to exclude
	 *
	 * @return static
	 */
	public function diff( array $items ) {
		return new static( array_diff( $this->items, $items ) );
	}

	/**
	 * Execute a callback over each item.
	 *
	 * @param  callable $callback Callback to be mapped to each item
	 *
	 * @return $this
	 */
	public function each( callable $callback ) {
		foreach ( $this->items as $key => $item ) {
			$callback( $item, $key );
		}

		return $this;
	}

	/**
	 * Get all items except for those with the specified keys.
	 *
	 * @param array|string $keys Key(s) to exclude
	 *
	 * @return static
	 */
	public function except( $keys ) {
		$keys = (array) $keys;

		return $this->filter(
			function ( $value, $key ) use ( $keys ) {
				return ! in_array( $key, $keys, true );
			}
		);
	}

	/**
	 * Run a filter over each of the items.
	 *
	 * @param callable|null $callback Filter callback
	 *
	 * @return static
	 */
	public function filter( callable $callback = null ) {
		if ( null === $callback ) {
			return new static( array_filter( $this->items ) );
		}

		return new static( array_filter( $this->items, $callback, ARRAY_FILTER_USE_BOTH ) );
	}

	/**
	 * Get the first item from the collection.
	 *
	 * @return mixed
	 */
	public function first() {
		return $this->slice( 0, 1 )->shift();
	}

	/**
	 * Flip the items in the collection.
	 *
	 * @return static
	 */
	public function flip() {
		return new static( array_flip( $this->items ) );
	}

	/**
	 * Remove one or more items from the collection by key.
	 *
	 * @param  string|array $keys Key(s) to exclude
	 *
	 * @return $this
	 */
	public function forget( $keys ) {
		foreach ( (array) $keys as $key ) {
			$this->offsetUnset( $key );
		}

		return $this;
	}

	/**
	 * "Paginate" the collection by slicing it into a smaller collection.
	 *
	 * @param  int $page Page number
	 * @param  int $perPage Number of items per page
	 *
	 * @return static
	 */
	public function forPage( $page, $perPage ) {
		$offset = max( 0, ( $page - 1 ) * $perPage );

		return $this->slice( $offset, $perPage );
	}

	/**
	 * Get an item from the collection by key.
	 *
	 * @param mixed $key Key used to find item
	 * @param mixed $default Default value to return if item doesn't exist
	 *
	 * @return mixed
	 */
	public function get( $key, $default = null ) {

		$value = $default;

		if ( $this->offsetExists( $key ) ) {
			$value = $this->items[ $key ];
		}

		return $value;
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return \ArrayIterator
	 */
	#[\ReturnTypeWillChange]
	public function getIterator() {
		return new \ArrayIterator( $this->items );
	}

	/**
	 * Group an associative array by a field.
	 *
	 * @param callable|string $groupBy Property or index to group by
	 *
	 * @return static
	 */
	public function groupBy( $groupBy ) {

		$results = [];
		foreach ( $this->items as $item ) {
			if ( is_array( $item ) && array_key_exists( $groupBy, $item ) ) {
				$results[ $item[ $groupBy ] ][] = $item;
			} elseif ( is_object( $item ) && property_exists( $item, $groupBy ) ) {
				$results[ $item->{$groupBy} ][] = $item;
			}
		}

		return new static( $results );
	}

	/**
	 * Determine if one or more items exist in the collection by key.
	 *
	 * @param mixed $key Key for which to check
	 *
	 * @return bool
	 */
	public function has( $key ) {
		$keys = is_array( $key ) ? $key : func_get_args();
		foreach ( $keys as $value ) {
			if ( ! $this->offsetExists( $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Concatenate values of a given key as a string.
	 *
	 * @param string $glue String used to join the items
	 *
	 * @return string
	 */
	public function implode( $glue = null ) {
		return implode( $glue, $this->items );
	}

	/**
	 * Index an associative array by a field.
	 *
	 * @param callable|string $indexBy Property or index used to index.
	 *
	 * @return static
	 */
	public function indexBy( $indexBy ) {

		$results = [];
		foreach ( $this->items as $item ) {
			if ( is_array( $item ) && array_key_exists( $indexBy, $item ) ) {
				$results[ $item[ $indexBy ] ] = $item;
			} elseif ( is_object( $item ) && property_exists( $item, $indexBy ) ) {
				$results[ $item->{$indexBy} ] = $item;
			}
		}

		return new static( $results );
	}

	/**
	 * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
	 * to the end of the array.
	 *
	 * @param mixed $key The key after which the items will be inserted
	 * @param array $value An array containing the key(s) and value(s) to be inserted
	 *
	 * @return $this
	 */
	public function insertAfter( $key, array $value ) {

		if ( ! $this->has( $key ) ) {
			return $this->push( $value );
		}

		$index       = array_search( $key, array_keys( $this->items ), true );
		$pos         = false === $index ? count( $this->items ) : $index + 1;
		$this->items = array_merge( array_slice( $this->items, 0, $pos ), $value, array_slice( $this->items, $pos ) );

		return $this;
	}

	/**
	 * Insert a value or key/value pair before a specific key in an array.  If key doesn't exist, value is prepended
	 * to the beginning of the array.
	 *
	 * @param mixed $key The key before which the items will be inserted
	 * @param array $value An array containing the key(s) and value(s) to be inserted
	 *
	 * @return $this
	 */
	public function insertBefore( $key, array $value ) {

		if ( ! $this->has( $key ) ) {
			return $this->prepend( $value );
		}

		$pos         = (int) array_search( $key, array_keys( $this->items ), true );
		$this->items = array_merge( array_slice( $this->items, 0, $pos ), $value, array_slice( $this->items, $pos ) );

		return $this;
	}

	/**
	 * Intersect the collection with the given items.
	 *
	 * @param array $items Items with which to intersect
	 *
	 * @return static
	 */
	public function intersect( array $items ) {
		return new static( array_intersect( $this->items, $items ) );
	}

	/**
	 * Intersect the collection with the given items by key.
	 *
	 * @param array $keys Keys with which to intersect
	 *
	 * @return static
	 */
	public function intersectByKeys( array $keys ) {
		return new static( array_intersect_key( $this->items, $keys ) );
	}

	/**
	 * Convert the object into something JSON serializable.
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->items;
	}

	/**
	 * Get the keys of the collection items.
	 *
	 * @return static
	 */
	public function keys() {
		return array_keys( $this->items );
	}

	/**
	 * Get the last item from the collection.
	 *
	 * @return mixed
	 */
	public function last() {
		return $this->slice( - 1, 1 )->pop();
	}

	/**
	 * Run the map over each of the items.
	 *
	 * @param callable $callback Callback to map to each item
	 *
	 * @return static
	 */
	public function map( callable $callback ) {
		$keys  = array_keys( $this->items );
		$items = array_map( $callback, $this->items, $keys );

		return new static( array_combine( $keys, $items ) );
	}

	/**
	 * Merge the collection with the given items.
	 *
	 * @param  array $items Items to merge
	 *
	 * @return static
	 */
	public function merge( array $items ) {
		return new static( array_merge( $this->items, $items ) );
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param mixed $key Key for which to check
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $key ) {
		return array_key_exists( $key, $this->items );
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param mixed $key Key used to find item
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $key ) {
		return $this->items[ $key ];
	}

	/**
	 * Set the item at the given offset.
	 *
	 * @param mixed $key Key to set
	 * @param mixed $value Value to set
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $key, $value ) {
		if ( null === $key ) {
			$this->items[] = $value;
		} else {
			$this->items[ $key ] = $value;
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param mixed $key Key to unset
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $key ) {
		unset( $this->items[ $key ] );
	}

	/**
	 * Get the items with the specified keys.
	 *
	 * @param array $keys Keys to fetch
	 *
	 * @return static
	 */
	public function only( $keys ) {
		return $this->filter(
			function ( $value, $key ) use ( $keys ) {
				return in_array( $key, $keys, true );
			}
		);
	}

	/**
	 * Get the values of a given key.
	 *
	 * @param  string|array|null $key Key to fetch
	 * @param  string|null       $indexKey Key to use as index
	 *
	 * @return static
	 */
	public function pluck( $key, $indexKey = null ) {
		return new static( array_column( $this->items, $key, $indexKey ) );
	}

	/**
	 * Get and remove the last item from the collection.
	 *
	 * @return mixed
	 */
	public function pop() {
		return array_pop( $this->items );
	}

	/**
	 * Push an item onto the beginning of the collection.
	 *
	 * @param  mixed $value Value to prepend
	 *
	 * @return $this
	 */
	public function prepend( $value ) {
		array_unshift( $this->items, $value );

		return $this;
	}

	/**
	 * Get and remove an item from the collection.
	 *
	 * @param mixed $key Key by which to fetch
	 * @param mixed $default Value to return if item doesn't exist
	 *
	 * @return mixed
	 */
	public function pull( $key, $default = null ) {
		$value = $this->get( $key, $default );
		$this->offsetUnset( $key );

		return $value;
	}

	/**
	 * Push an item onto the end of the collection.
	 *
	 * @param mixed $value Item to append
	 *
	 * @return $this
	 */
	public function push( $value ) {
		$this->offsetSet( null, $value );

		return $this;
	}

	/**
	 * Put an item in the collection by key.
	 *
	 * @param mixed $key Key to set
	 * @param mixed $value Value to set
	 *
	 * @return $this
	 */
	public function put( $key, $value ) {
		$this->offsetSet( $key, $value );

		return $this;
	}

	/**
	 * Get one or a specified number of items randomly from the collection.
	 *
	 * @param int $count Number of items to return
	 *
	 * @return static
	 */
	public function random( $count = 1 ) {
		$values = [];
		$keys   = (array) array_rand( $this->items, min( $count, $this->count() ) );
		foreach ( $keys as $key ) {
			$values[ $key ] = $this->offsetGet( $key );
		}

		return new static( $values );
	}

	/**
	 * Reverse items order.
	 *
	 * @return static
	 */
	public function reverse() {
		return new static( array_reverse( $this->items, true ) );
	}

	/**
	 * Search the collection for a given value and return the corresponding key if successful.
	 *
	 * @param mixed $value Value by which to search
	 * @param bool  $strict Whether or not to use a strict comparison
	 *
	 * @return mixed
	 */
	public function search( $value, $strict = false ) {
		return array_search( $value, $this->items, $strict );
	}

	/**
	 * Get and remove the first item from the collection.
	 *
	 * @return mixed
	 */
	public function shift() {
		return array_shift( $this->items );
	}

	/**
	 * Shuffle the items in the collection.
	 *
	 * @return static
	 */
	public function shuffle() {
		return new static( shuffle( $this->items ) );
	}

	/**
	 * Slice the underlying collection array.
	 *
	 * @param int $offset Numeric offset
	 * @param int $length Length of returned collection
	 *
	 * @return static
	 */
	public function slice( $offset, $length = null ) {
		return new static( array_slice( $this->items, $offset, $length, true ) );
	}


	/**
	 * Sort through each item with a callback.
	 *
	 * @param callable|null $callback Callback used for sorting
	 *
	 * @return static
	 */
	public function sort( callable $callback = null ) {
		$items = $this->items;
		$callback ? uasort( $items, $callback ) : asort( $items );

		return new static( $items );
	}

	/**
	 * Sort the collection keys.
	 *
	 * @param int  $options Can be SORT_REGULAR (0), SORT_NUMERIC (1), SORT_STRING (2), SORT_LOCALE_STRING (5)
	 * @param bool $descending True to sort descending, false to sort ascending
	 *
	 * @return static
	 */
	public function sortKeys( $options = SORT_REGULAR, $descending = false ) {
		$items = $this->items;
		$descending ? krsort( $items, $options ) : ksort( $items, $options );

		return new static( $items );
	}

	/**
	 * Take the first or last {$limit} items.
	 *
	 * @param int $limit Number of items to return (negative number returns last items, positive returns first items)
	 *
	 * @return static
	 */
	public function take( $limit ) {
		if ( $limit < 0 ) {
			return $this->slice( $limit, abs( $limit ) );
		}

		return $this->slice( 0, $limit );
	}

	/**
	 * Pass the collection to the given callback and then return it.
	 *
	 * @param callable $callback Callback to use
	 *
	 * @return $this
	 */
	public function tap( callable $callback ) {
		$callback( new static( $this->items ) );

		return $this;
	}

	/**
	 * Get the collection of items as an array.
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->all();
	}

	/**
	 * Get the collection of items as JSON.
	 *
	 * @param int $options Can be JSON_PRETTY_PRINT or other such constants.
	 *
	 * @return string
	 */
	public function toJson( $options = 0 ) {
		return json_encode( $this->jsonSerialize(), $options );
	}

	/**
	 * Convert the collection to its string representation.
	 *
	 * @return string
	 */
	public function toString() {
		return $this->toJson();
	}

	/**
	 * Transform each item in the collection using a callback.
	 *
	 * @param callable $callback Callback to use
	 *
	 * @return $this
	 */
	public function transform( callable $callback ) {
		$this->items = $this->map( $callback )->all();

		return $this;
	}

	/**
	 * Return only unique items from the collection array.
	 *
	 * @return static
	 */
	public function unique() {
		return new static( array_unique( $this->items ) );
	}

	/**
	 * Reset the keys on the underlying array.
	 *
	 * @return static
	 */
	public function values() {
		return array_values( $this->items );
	}

	/**
	 * Apply the callback if the value is truthy. Otherwise, call the fallback (if set).
	 *
	 * @param bool     $value Evaluated conditional
	 * @param callable $callback Callback to apply
	 * @param callable $fallback Fallback value to return
	 *
	 * @return mixed
	 */
	public function when( $value, callable $callback, callable $fallback = null ) {
		return $value ? $callback( $this, $value ) : ( $fallback ? $fallback( $this, $value ) : $this );
	}

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param string $key Key to fetch
	 * @param mixed  $operator Operator to use
	 * @param mixed  $value Value to check
	 *
	 * @return static
	 */
	public function where( $key, $operator, $value = null ) {

		if ( func_num_args() === 2 ) {
			$value    = $operator;
			$operator = '=';
		}

		return $this->filter(
			function ( $item ) use ( $key, $operator, $value ) {

				$retrieved = isset( $item[ $key ] ) ? $item[ $key ] : null;

				switch ( $operator ) {
					default:
					case '=':
					case '==':
						return $retrieved == $value;
					case '!=':
					case '<>':
						return $retrieved != $value;
					case '<':
						return $retrieved < $value;
					case '>':
						return $retrieved > $value;
					case '<=':
						return $retrieved <= $value;
					case '>=':
						return $retrieved >= $value;
					case '===':
						return $retrieved === $value;
					case '!==':
						return $retrieved !== $value;
				}

			}
		);
	}

	/**
	 * Convert the collection to its string representation.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}

}
