<?php

namespace NewfoldLabs\WP\Module\Survey\WPAdmin;

use NewfoldLabs\WP\Module\Survey\WPAdmin\Listeners\DataAttrListener;
use NewfoldLabs\WP\Module\Survey\WPAdmin\Listeners\SurveyQueueListener;

/**
 * Manages all the wp-admin related functionalities for the module.
 */
class WPAdmin {
	/**
	 * Constructor for the WPAdmin class.
	 */
	public function __construct() {
		new DataAttrListener();
		new SurveyQueueListener();
	}
}
