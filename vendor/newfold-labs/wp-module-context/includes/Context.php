<?php
namespace NewfoldLabs\WP\Context;

use function WP_Forge\Helpers\dataGet;
use function WP_Forge\Helpers\dataSet;

/**
 * This class adds context functionality.
 **/
class Context {

	/**
	 * Context array
	 *
	 * @var Array
	 */
	public static $context = array();

	/**
	 * All contexts
	 *
	 * @return Array $context - all context
	 */
	public static function all() {
		return self::$context;
	}

	/**
	 * Get the context value
	 *
	 * @param String $name - the name of the context to get
	 * @param String $default - the default value if not defined
	 * @return Array $context - value of the named context
	 */
	public static function get( $name, $default = null ) {
		return dataGet( self::$context, $name, $default );
	}

	/**
	 * Set a context value
	 *
	 * @param String $name - the name of the context to set
	 * @param String $value - the value to set
	 */
	public static function set( $name, $value ) {
		dataSet( self::$context, $name, $value );
	}
}
