<?php

namespace NewfoldLabs\WP\Module\Survey\Models;

use NewfoldLabs\WP\Module\Survey\Data\Options;

/**
 * Model for the Toast survey.
 */
class Toast {
	/**
	 * The type of survey.
	 *
	 * @var string
	 */
	public static $type = 'toast';

	/**
	 * The action that triggered the survey (source).
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * The category of the survey.
	 *
	 * @var string
	 */
	protected $category;

	/**
	 * Additional data for the survey.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The heading to be displayed on the Toast.
	 *
	 * @var string
	 */
	protected $heading;

	/**
	 * The subheading or description to be displayed on the Toast.
	 *
	 * @var string
	 */
	protected $subheading;

	/**
	 * Constructor for the Toast class.
	 *
	 * @param string $action The action that triggered the survey (source).
	 * @param string $category The category of the survey.
	 * @param array  $data Additional data for the survey.
	 * @param string $heading The heading to be displayed on the Toast.
	 * @param string $subheading The subheading or description to be displayed on the Toast.
	 */
	public function __construct( $action, $category, $data = array(), $heading, $subheading, ) {
		$this->action     = $action;
		$this->category   = $category;
		$this->data       = $data;
		$this->heading    = $heading;
		$this->subheading = $subheading;
	}

	/**
	 * Adds the Toast survey in a queue to be displayed on the next page load.
	 *
	 * @return boolean
	 */
	public function queue() {
		$option_name = Options::get_option_name( 'queue' );

		$surveys = get_option( $option_name, array() );
		if ( ! isset( $surveys[ self::$type ] ) ) {
			$surveys[ self::$type ] = array();
		}

		array_push(
			$surveys[ self::$type ],
			array(
				'action'     => $this->action,
				'category'   => $this->category,
				'data'       => $this->data,
				'heading'    => $this->heading,
				'subheading' => $this->subheading,
			)
		);

		update_option( $option_name, $surveys );

		return true;
	}
}
