<?php

namespace NewfoldLabs\WP\Module\PLS\Utilities;

use NewfoldLabs\WP\Module\Data\HiiveConnection;

/**
 * Class for handling API requests to Hiive services.
 */
class HiiveUtility {
	/**
	 * The base URL for the API requests.
	 *
	 * @var string
	 */
	private $api_base_url;

	/**
	 * The specific API endpoint to send the request to.
	 *
	 * @var string
	 */
	private $endpoint;

	/**
	 * The request body to be used in POST/PUT/PATCH requests.
	 *
	 * @var array
	 */
	private $body;

	/**
	 * The HTTP method to be used (e.g., 'GET', 'POST', 'PUT', 'PATCH', 'DELETE').
	 *
	 * @var string
	 */
	private $method;

	/**
	 * Constructor for HiiveUtility.
	 *
	 * @param string $endpoint The API endpoint to send the request to.
	 * @param array  $body     The body of the request (used in POST/PUT/PATCH requests).
	 * @param string $method   The HTTP method for the request (e.g., 'GET', 'POST', 'PUT', 'PATCH', 'DELETE').
	 */
	public function __construct( $endpoint, $body = array(), $method = 'POST' ) {
		if ( ! defined( 'NFD_HIIVE_URL' ) ) {
			define( 'NFD_HIIVE_URL', 'https://hiive.cloud/api' );
		}
		$this->api_base_url = constant( 'NFD_HIIVE_URL' );

		$this->endpoint = $endpoint;
		$this->body     = $body;
		$this->method   = strtoupper( $method );
	}

	/**
	 * Sends the remote request to the specified Hiive API endpoint using the defined method and body.
	 *
	 * @return mixed|WP_Error The response from the API or a WP_Error if the request fails.
	 */
	public function send_request() {

		$url = $this->get_request_url();

		// Check the connection to ensure the API is reachable
		if ( ! HiiveConnection::is_connected() ) {
			return new \WP_Error(
				'nfd_pls_error',
				__( 'Failed to connect to the API', 'wp-module-pls' )
			);
		}

		// Set up the request arguments, including method, headers, and timeout
		$args = array(
			'method'  => $this->method,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . HiiveConnection::get_auth_token(),
			),
			'timeout' => 30,
		);

		// For POST, PUT, and PATCH requests, include the body in the request
		if ( in_array( $this->method, array( 'POST', 'PUT', 'PATCH' ), true ) ) {
			$args['body'] = wp_json_encode( $this->body );
		}

		// For GET and DELETE requests, add the body as query parameters to the URL (if body exists)
		if ( in_array( $this->method, array( 'GET', 'DELETE' ), true )
		&& ! empty( $this->body ) ) {
			$url = add_query_arg( $this->body, $url );
		}

		$response = wp_remote_request( $url, $args );

		// If the request returns a WP_Error, return the error
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Check the response code to ensure it's a successful response (status code 200-299)
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code < 200 || $response_code >= 300 ) {
			return new \WP_Error(
				'nfd_pls_error',
				__( 'API returned a non-success status code: ', 'wp-module-pls' ) . $response_code
			);
		}

		// Return the body of the response
		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Return the request URL
	 * Construct the full URL by appending the endpoint to the base URL
	 *
	 * @return string
	 */
	public function get_request_url() {
		return $this->api_base_url . $this->endpoint;
	}
}
