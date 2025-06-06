<?php

namespace NewfoldLabs\WP\Module\Survey\WPAdmin\Listeners;

use NewfoldLabs\WP\Module\Survey\Data\Options;

/**
 * Manages all the survey queue wp-option listening related functionalities for the module.
 */
class SurveyQueueListener {
	/**
	 * The list of surveys in the queue.
	 *
	 * @var array
	 */
	private $surveys = array();

	/**
	 * Constructor for the SurveyQueue class.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'load_surveys' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_surveys_script' ) );
	}

	/**
	 * Loads surveys from the queue and clears it.
	 *
	 * @return array
	 */
	public function load_surveys() {
		$option_name = Options::get_option_name( 'queue' );

		$this->surveys = get_option( $option_name, array() );
		update_option( $option_name, array() );

		return $this->surveys;
	}

	/**
	 * Enqueues the surveys script.
	 *
	 * @return void
	 */
	public function enqueue_surveys_script() {
		if ( ! empty( $this->surveys ) ) {
			$asset_file = NFD_SURVEY_BUILD_DIR . '/surveys.asset.php';

			if ( is_readable( $asset_file ) ) {

				$asset = include $asset_file;

				wp_register_script(
					'nfd-survey-surveys',
					NFD_SURVEY_BUILD_URL . '/surveys.js',
					array_merge( $asset['dependencies'], array() ),
					$asset['version'],
					true
				);

				wp_register_style(
					'nfd-survey-surveys',
					NFD_SURVEY_BUILD_URL . '/surveys.css',
					array(),
					$asset['version']
				);

				wp_add_inline_script(
					'nfd-survey-surveys',
					'var nfdSurveySurveys =' . wp_json_encode(
						array(
							'queue' => $this->surveys,
						)
					) . ';',
					'before'
				);

				wp_enqueue_script( 'nfd-survey-surveys' );
				wp_enqueue_style( 'nfd-survey-surveys' );
			}
		}
	}
}
