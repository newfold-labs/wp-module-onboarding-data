<?php

namespace NewfoldLabs\WP\Module\AI\Utils;

use NewfoldLabs\WP\Module\Data\SiteCapabilities;

/**
 * The utility pass through for interacting with the
 * AI service
 */
class AISearchUtil {

	/**
	 * The function to check capabilities for module AI
	 */
	private static function check_capabilities() {
		$capability = new SiteCapabilities();

		$help_enabled = $capability->get( 'canAccessHelpCenter' );

		return $help_enabled;
	}

	/**
	 * The function to check just Help capability
	 */
	private static function check_help_capability() {
		$capability = new SiteCapabilities();

		$help_enabled = $capability->get( 'canAccessHelpCenter' );

		return $help_enabled;
	}

	/**
	 * The function to proxy to the AI service and get a response
	 *
	 * @param string     $hiive_token The Hive token
	 * @param string     $user_prompt The user search query
	 * @param string     $identifier  The identifier for the caller
	 * @param array|null $extra       Extra parameters to be included
	 * @return array
	 */
	public static function get_search_results(
		string $hiive_token,
		string $user_prompt,
		string $identifier,
		array $extra = null
	) {

		if ( ! self::check_capabilities() ) {
			return array(
				'error' => __( 'We are unable to process the request at this moment' ),
			);
		}

		$response = wp_remote_post(
			NFD_AI_SERVICE_BASE,
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'timeout' => 60,
				'body'    => wp_json_encode(
					array(
						'hiivetoken' => $hiive_token,
						'prompt'     => $user_prompt,
						'identifier' => $identifier,
						'extra'      => $extra,
					)
				),
			)
		);
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return array(
				'error' => __( 'We are unable to process the request at this moment' ),
			);
		}

		$parsed_response = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $parsed_response['status'] ) && 'Failure' === $parsed_response['status'] ) {
			return array(
				'error' => __( 'We are unable to process the request at this moment' ),
			);
		}

		try {
			return array(
				'result'  => $parsed_response['payload']['choices'],
				'post_id' => $parsed_response['payload']['postId'],
			);
		} catch ( \Exception $exception ) {
			return array(
				'error' => __( 'We are unable to process the request at this moment' ),
			);
		}
	}

	/**
	 * The function to get the default results from the proxy
	 *
	 * @param string $hiive_token The Hive token
	 * @return array
	 */
	public static function get_default_search_results( $hiive_token ) {
		if ( ! self::check_help_capability() ) {
			return array(
				'error' => __( 'We are unable to process the request at this moment' ),
			);
		}

		$response = wp_remote_post(
			NFD_AI_SERVICE_BASE . 'defaultResults',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'timeout' => 60,
				'body'    => wp_json_encode(
					array(
						'hiivetoken' => $hiive_token,
					)
				),
			)
		);
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return array(
				'error' => __( 'We are unable to process the request at this moment' ),
			);
		}

		$parsed_response = json_decode( wp_remote_retrieve_body( $response ), true );

		try {
			return array(
				'posts' => $parsed_response['payload']['posts'],
			);
		} catch ( \Exception $exception ) {
			return array(
				'error' => __( 'We are unable to process the request at this moment' ),
			);
		}
	}
}
