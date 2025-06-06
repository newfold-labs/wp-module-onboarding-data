<?php

namespace NewfoldLabs\WP\Module\Survey;

use NewfoldLabs\WP\Module\Survey\WPAdmin\WPAdmin;
use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * Manages all the functionalities for the module.
 */
class Survey {
	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor for the Survey class.
	 *
	 * @param Container $container The module container.
	 */
	public function __construct( Container $container ) {

		$this->container = $container;

		if ( Permissions::is_authorized_admin() ) {
			new WPAdmin();
		}
	}
}
