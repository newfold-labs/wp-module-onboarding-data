<?php

namespace NewfoldLabs\WP\Module\Data\WonderBlocks;

use NewfoldLabs\WP\Module\Data\HiiveConnection;
use NewfoldLabs\WP\Module\Data\WonderBlocks\Requests\Fetch;
use NewfoldLabs\WP\Module\Data\WonderBlocks\Requests\Request;

/**
 * Class WonderBlocks
 *
 * Handler for WonderBlock requests.
 */
class WonderBlocks {

	/**
	 * Handle a WonderBlock fetch request.
	 *
	 * @param Fetch $request The request object.
	 * @return array|false
	 */
	public static function fetch( Fetch $request ) {

		// Generate a unique hash for the request object.
		$hash     = $request->get_md5_hash();
		$endpoint = $request->get_endpoint();
		// Do not use cache in development mode.
		if ( ! self::is_dev_mode() ) {
			// If the transient exists, return data from the transient. Add endpoint for batch clearing endpoint transients.
			$data = get_transient( "nfd_data_wb_{$endpoint}_{$hash}" );
			if ( ! empty( $data ) ) {
				return $data;
			}
		}

		$url = $request->get_url();
		if ( empty( $url ) ) {
			return false;
		}

		// Populate valid request arguments.
		$args = array(
			'headers' => array(
				'X-Hiive-Token' => HiiveConnection::get_auth_token(),
			),
			'body'    => $request->get_args(),
			'method'  => \WP_REST_Server::READABLE,
		);

		$response = wp_remote_request(
			$url,
			$args
		);
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data || ! is_array( $data ) || ! isset( $data['data'] ) ) {
			return false;
		}

		// Cache the response data if specified.
		if ( $request->should_cache() ) {
			set_transient( "nfd_data_wb_{$endpoint}_{$hash}", $data['data'], $request->get_cache_timeout() );
		}

		return $data['data'];
	}

	/**
	 * Check is the NFD_DATA_WB_DEV_MODE defined and defined as true.
	 */
	protected static function is_dev_mode(): bool {
		return defined( 'NFD_DATA_WB_DEV_MODE' )
			&& constant( 'NFD_DATA_WB_DEV_MODE' );
	}

	/**
	 * Clear the cache related a particular request object.
	 *
	 * @param Request $request An instance of the Request class.
	 */
	public static function clear_cache( Request $request ): bool {
		$endpoint = $request->get_endpoint();
		$hash     = $request->get_md5_hash();
		return delete_transient( "nfd_data_wb_{$endpoint}_{$hash}" );
	}
}
