<?php

namespace NewfoldLabs\WP\Context;

use NewfoldLabs\WP\Context\Context;

/**
 * Helper Get Context Method
 *
 * @param String $name - the name of the context to get
 * @param String $default - the default value if not defined
 * @return Array $context - value of the named context
 */
function getContext( $name, $default = null ) {
	return Context::get( $name, $default );
}

/**
 * Helper Set Context Method
 *
 * @param String $name - the name of the context to set
 * @param String $value - the value to set
 */
function setContext( $name, $value ) {
	Context::set( $name, $value );
}
