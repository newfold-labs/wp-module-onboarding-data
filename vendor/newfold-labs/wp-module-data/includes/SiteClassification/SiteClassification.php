<?php

namespace NewfoldLabs\WP\Module\Data\SiteClassification;

use NewfoldLabs\WP\Module\Data\HiiveWorker;

/**
 * Class SiteClassification
 *
 * Class that handles fetching and caching of site classification data.
 *
 * @package NewfoldLabs\WP\Module\Data
 */
class SiteClassification {
	/**
	 * Name of the site classification data transient.
	 *
	 * @var string
	 */
	public static $transient_name = 'nfd_data_site_classification';

	/**
	 * Retrieves the site classification data.
	 *
	 * @return array
	 */
	public static function get() {
		// Get the current locale of the site.
		$locale = str_replace( '_', '-', get_locale() );

		$transient_name = self::$transient_name . '-' . $locale;
		// Checks the transient for data.
		$classification = get_transient( $transient_name );
		if ( false !== $classification ) {
			return $classification;
		}

		// Fetch data from the worker.
		$classification = self::fetch_from_worker( $locale );
		if ( ! empty( $classification ) ) {
			set_transient( $transient_name, $classification, DAY_IN_SECONDS );
			return $classification;
		}

		// Fetch data from the static JSON file.
		$classification = self::fetch_from_static_file( $locale );
		if ( ! empty( $classification ) ) {
			$classification['static'] = true;
			set_transient( $transient_name, $classification, HOUR_IN_SECONDS );
			return $classification;
		}

		// Cache an empty array for an hour if no data could be retrieved.
		set_transient( $transient_name, array(), HOUR_IN_SECONDS );
		return array();
	}

	/**
	 * Fetch site classification data from the CF worker.
	 *
	 * @param string $locale The locale in kebab case.
	 * @return array
	 */
	public static function fetch_from_worker( $locale = 'en-US' ) {
		$worker   = new HiiveWorker( 'site-classification' );
		$response = $worker->request(
			'GET',
			array(
				'headers' => array(
					'Accept' => 'application/json',
				),
				'body'    => array(
					'locale' => $locale,
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( ! $data || ! is_array( $data ) ) {
			return array();
		}

		return $data;
	}

	/**
	 * Fetch site classification data from the static JSON file.
	 *
	 * @param string $locale The locale in kebab case.
	 * @return array
	 */
	public static function fetch_from_static_file( $locale = 'en-US' ) {
		$filename = realpath( __DIR__ . "/../Data/Static/site-classification-{$locale}.json" );

		if ( ! file_exists( $filename ) ) {
			if ( 'en-US' === $locale ) {
				return array();
			}

			// If the file does not exist and the locale is not en-US, then default to the en-US file.
			$filename = realpath( __DIR__ . '/../Data/Static/site-classification-en-US.json' );
			if ( ! file_exists( $filename ) ) {
				return array();
			}
		}

		$data = json_decode( file_get_contents( $filename ), true );
		if ( ! $data ) {
			return array();
		}

		return $data;
	}
}
