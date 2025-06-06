<?php

namespace NewfoldLabs\WP\Module\PLS\WPCLI;

use NewfoldLabs\WP\Module\PLS\WPCLI\Handlers\PLSCommandHandler;
use WP_CLI;

/**
 * Class WPCLI
 *
 * Handles registering WP-CLI commands for the PLS module.
 */
class WPCLI {
	/**
	 * Constructor for WPCLI class.
	 */
	public function __construct() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'pls', PLSCommandHandler::class );
		}
	}
}
