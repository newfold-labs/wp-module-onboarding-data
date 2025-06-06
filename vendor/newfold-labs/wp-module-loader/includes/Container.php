<?php

namespace NewfoldLabs\WP\ModuleLoader;

class Container extends \NewfoldLabs\Container\Container {

	/**
	 * Get plugin data.
	 *
	 * @return Plugin
	 */
	public function plugin() {
		return $this->get( 'plugin' );
	}

}
