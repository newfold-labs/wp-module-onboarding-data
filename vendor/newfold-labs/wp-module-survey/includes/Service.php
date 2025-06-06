<?php

namespace NewfoldLabs\WP\Module\Survey;

use NewfoldLabs\WP\Module\Survey\Models\Toast;

/**
 * Provides CRUD operations for all the surveys in the module.
 */
class Service {
	/**
	 * Creates and queues a Toast survey for display on the next admin page load.
	 *
	 * @param string $action The action that triggered the survey (source).
	 * @param string $category The category of the survey.
	 * @param array  $data Additional data for the survey.
	 * @param string $heading The heading to be displayed on the Toast.
	 * @param string $subheading The subheading or description to be displayed on the Toast.
	 */
	public function create_toast_survey( $action, $category, $data, $heading, $subheading ) {
		$survey = new Toast( $action, $category, $data, $heading, $subheading );
		$survey->queue();
		return $survey;
	}
}
