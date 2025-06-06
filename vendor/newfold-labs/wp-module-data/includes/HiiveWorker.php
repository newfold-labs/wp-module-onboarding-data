<?php

namespace NewfoldLabs\WP\Module\Data;

/**
 * Class HiiveWorker
 *
 * Base class for Hiive Worker related actions.
 *
 * @package NewfoldLabs\WP\Module\Data
 */
class HiiveWorker {

	/**
	 * The URL of the worker.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * The endpoint of the worker URL.
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * HiiveWorker constructor.
	 *
	 * @param string $endpoint The endpoint of the worker URL.
	 */
	public function __construct( $endpoint ) {

		if ( ! defined( 'NFD_HIIVE_BASE_URL' ) ) {
			define( 'NFD_HIIVE_BASE_URL', 'https://hiive.cloud' );
		}

		$this->endpoint = $endpoint;
		$this->url      = NFD_HIIVE_BASE_URL . '/workers/' . $endpoint;
	}

	/**
	 * Places an HTTP request to the Hiive CF worker.
	 *
	 * @param string $method The HTTP request method (GET, POST, ....).
	 * @param array  $args The HTTP request arguments (headers, body, ...)
	 * @return array
	 */
	public function request( $method, $args ) {
		$args['method'] = $method;

		return \wp_remote_request(
			$this->url,
			$args
		);
	}
}
