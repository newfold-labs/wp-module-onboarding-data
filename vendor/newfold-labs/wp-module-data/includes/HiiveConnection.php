<?php

namespace NewfoldLabs\WP\Module\Data;

use NewfoldLabs\WP\Module\Data\Helpers\Plugin as PluginHelper;
use NewfoldLabs\WP\Module\Data\Helpers\Transient;
use WP_Error;
use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * Manages a Hiive connection instance and interactions with it
 */
class HiiveConnection implements SubscriberInterface {

	/**
	 * Hiive API url
	 *
	 * @var string
	 */
	private $api;

	/**
	 * Authentication token for data api
	 *
	 * @var string
	 */
	private $token;


	/**
	 * Whether connection attempts are currently throttled
	 *
	 * @var bool
	 */
	private $throttled;

	/**
	 * The throttle
	 *
	 * @var bool
	 */
	protected $throttle;

	/**
	 * Construct
	 */
	public function __construct() {

		if ( ! defined( 'NFD_HIIVE_URL' ) ) {
			define( 'NFD_HIIVE_URL', 'https://hiive.cloud/api' );
		}

		$this->api = constant( 'NFD_HIIVE_URL' );
	}

	/**
	 * Register the hooks required for site verification
	 *
	 * @return void
	 */
	public function register_verification_hooks() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		add_action( 'wp_ajax_nopriv_nfd-hiive-verify', array( $this, 'ajax_verify' ) );
	}

	/**
	 * Set up REST API routes
	 *
	 * @hooked rest_api_init
	 */
	public function rest_api_init(): void {
		$controller = new API\Verify( $this );
		$controller->register_routes();
	}

	/**
	 * Process the admin-ajax request
	 *
	 * Hiive will first attempt to verify using the REST API, and fallback to this AJAX endpoint on error.
	 *
	 * Token is generated in {@see self::connect()} using {@see md5()}.
	 *
	 * @hooked wp_ajax_nopriv_nfd-hiive-verify
	 *
	 * @return never
	 */
	public function ajax_verify() {
		// PHPCS: Ignore the nonce verification here – the token _is_ a nonce.
		// @phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = $_REQUEST['token'];

		$is_valid = $this->verify_token( $token );
		$status   = ( $is_valid ) ? 200 : 400;

		$data = array(
			'token' => $token,
			'valid' => $is_valid,
		);
		\wp_send_json( $data, $status );
	}

	/**
	 * Confirm whether verification token is valid
	 *
	 * Token is generated in {@see self::connect()} using {@see md5()}.
	 *
	 * @param string $token Token to verify
	 */
	public function verify_token( string $token ): bool {
		$saved_token = Transient::get( 'nfd_data_verify_token' );

		if ( $saved_token && $saved_token === $token ) {
			Transient::delete( 'nfd_data_verify_token' );

			return true;
		}

		return false;
	}

	/**
	 * Check whether site has established connection to hiive
	 *
	 * This is cleared whenever Hiive returns 401 unauthenticated {@see Data::delete_token_on_401_response()}.
	 *
	 * @used-by Data::init()
	 */
	public static function is_connected(): bool {
		return (bool) ( self::get_auth_token() );
	}

	/**
	 * Attempt to connect to Hiive
	 *
	 * @used-by Data::init()
	 * @used-by HiiveConnection::reconnect()
	 *
	 * @param string $path the path
	 * @param string $authorization the authorization
	 * @return Boolean success
	 */
	public function connect( string $path = '/sites/v2/connect', ?string $authorization = null ): bool {

		if ( $this->is_throttled() ) {
			return false;
		}

		$this->throttle();

		$token = md5( \wp_generate_password() );
		Transient::set( 'nfd_data_verify_token', $token, 5 * constant( 'MINUTE_IN_SECONDS' ) );

		$data                 = $this->get_core_data();
		$data['verify_token'] = $token;
		$data['plugins']      = ( new PluginHelper() )->collect_installed();

		$args = array(
			'body'     => \wp_json_encode( $data ),
			'headers'  => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'blocking' => true,
			'timeout'  => 30,
		);

		if ( $authorization ) {
			$args['headers']['Authorization'] = $authorization;
		}

		$attempts = intval( get_option( 'nfd_data_connection_attempts', 0 ) );
		\update_option( 'nfd_data_connection_attempts', $attempts + 1 );

		$response = \wp_remote_post( $this->api . $path, $args );
		$status   = \wp_remote_retrieve_response_code( $response );

		// Created = 201; Updated = 200
		if ( 201 === $status || 200 === $status ) {
			$body = json_decode( \wp_remote_retrieve_body( $response ) );
			if ( ! empty( $body->token ) ) {

				// Token is auto-encrypted using the `pre_update_option_nfd_data_token` hook.
				\update_option( 'nfd_data_token', $body->token );
				return true;
			}
		}
		return false;
	}

	/**
	 * Rename the site URL in Hiive.
	 *
	 * This performs almost the same request as {@see self::connect} but includes the Site authorization token,
	 * to verify this site is the owner of the existing site in Hiive, and Hiive pings back the new URL to verify
	 * the DNS points to this site.
	 */
	public function reconnect(): bool {
		return $this->connect( '/sites/v2/reconnect', 'Bearer ' . self::get_auth_token() );
	}

	/**
	 * Set the connection throttle
	 *
	 * @return void
	 */
	public function throttle() {
		$interval = $this->get_throttle_interval();

		$this->throttle = Transient::set( 'nfd_data_connection_throttle', true, $interval );
	}

	/**
	 * Determine the throttle interval based off number of connection attempts
	 *
	 * @return integer Time to wait until next connection attempt
	 */
	public function get_throttle_interval() {

		$attempts = intval( \get_option( 'nfd_data_connection_attempts', 0 ) );

		// Throttle intervals step-up:
		// Hourly for 4 hours
		// Twice a day for 3 days
		// Once a day for 3 days
		// Every 3 days for 3 times
		// Once a week
		if ( $attempts <= 4 ) {
			return HOUR_IN_SECONDS;
		} elseif ( $attempts <= 10 ) {
			return 12 * HOUR_IN_SECONDS;
		} elseif ( $attempts <= 13 ) {
			return DAY_IN_SECONDS;
		} elseif ( $attempts <= 16 ) {
			return 3 * DAY_IN_SECONDS;
		} else {
			return WEEK_IN_SECONDS;
		}
	}

	/**
	 * Check whether connection is throttled
	 *
	 * @return boolean
	 */
	public function is_throttled() {
		$this->throttled = Transient::get( 'nfd_data_connection_throttle' );

		return $this->throttled;
	}

	/**
	 * Synchronously send a single event and return the notifications.
	 *
	 * @used-by Events::create_item()
	 *
	 * @param Event $event the event
	 *
	 * @phpstan-type Notification_Array array{id:string,locations:array,query:string|null,expiration:int,content:string}
	 * @return array<Notification_Array>|WP_Error
	 */
	public function send_event( Event $event ) {

		$payload = array(
			'environment' => $this->get_core_data(),
			'events'      => array( $event ),
		);

		$hiive_response = $this->hiive_request( 'sites/v1/events', $payload );

		if ( is_wp_error( $hiive_response ) ) {
			return $hiive_response;
		}

		$status_code = \wp_remote_retrieve_response_code( $hiive_response );

		if ( ! in_array( $status_code, array( 200, 201 ), true ) ) {
			return new \WP_Error( $status_code, \wp_remote_retrieve_response_message( $hiive_response ) );
		}

		/**
		 * Sample shape.
		 *
		 * @var array{data:array{id:string,locations:array,query:string|null,expiration:int,content:string}} $response_payload
		 * */
		$response_payload = json_decode( \wp_remote_retrieve_body( $hiive_response ), true );

		return $response_payload['data'] ?? array();
	}

	/**
	 * Send events to the v2 events endpoint and return the list of successes and list of failures.
	 *
	 * @see SubscriberInterface::notify()
	 * @used-by EventManager::send()
	 *
	 * @param Event[] $events Array of Event objects representing the actions that occurred.
	 *
	 * @return array{succeededEvents:array,failedEvents:array}|WP_Error
	 */
	public function notify( $events ) {

		$payload = array(
			'environment' => $this->get_core_data(),
			'events'      => $events,
		);

		$hiive_response = $this->hiive_request( 'sites/v2/events', $payload );

		if ( \is_wp_error( ( $hiive_response ) ) ) {
			return $hiive_response;
		}

		if ( ! in_array( \wp_remote_retrieve_response_code( $hiive_response ), array( 200, 201, 500 ), true ) ) {
			return new WP_Error( \wp_remote_retrieve_response_code( $hiive_response ), \wp_remote_retrieve_response_message( $hiive_response ) );
		}

		$response_body = json_decode( wp_remote_retrieve_body( $hiive_response ), true );

		// If the response from Hiive is not shaped as expected, e.g. a more serious 500 error, return as an error, not as the expected array.
		if ( ! is_array( $response_body ) || ! array_key_exists( 'succeededEvents', $response_body ) || ! array_key_exists( 'failedEvents', $response_body ) ) {
			return new WP_Error( 'hiive_response', 'Response body does not contain succeededEvents and failedEvents keys.' );
		}

		return $response_body;
	}

	/**
	 * Send an HTTP request to Hiive and return the body of the request.
	 *
	 * Handles throttling and reconnection, clients should handle queueing if necessary.
	 *
	 * Defaults to POST. Override with `$args = array('method' => 'GET')`.
	 *
	 * @param string     $path The Hiive api path (after /api/).
	 * @param array|null $payload the payload
	 * @param array|null $args and args for the request
	 *
	 * @return array|WP_Error The response array or a WP_Error when no Hiive connection, no network connection, network requests disabled.
	 */
	public function hiive_request( string $path, ?array $payload = array(), ?array $args = array() ) {

		/**
		 * Add plugin name/version to user agent
		 *
		 * @see \WP_Http::request()
		 * @see https://developer.wordpress.org/reference/hooks/http_headers_useragent/
		 */
		add_filter( 'http_headers_useragent', array( $this, 'add_plugin_name_version_to_user_agent' ), 10, 2 );

		// If for some reason we are not connected, bail out now.
		// If we are not connected, the throttling logic should eventually reconnect.
		if ( ! self::is_connected() ) {
			return new WP_Error( 'hiive_connection', __( 'This site is not connected to the hiive.' ) );
		}

		$defaults = array(
			'method'  => 'POST',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
				'Authorization' => 'Bearer ' . self::get_auth_token(),
			),
			'timeout' => \wp_is_serving_rest_request() ? 15 : 60, // If we're responding to the frontend, we need to be quick.
		);

		$parsed_args = \wp_parse_args( $args ?? array(), $defaults );

		if ( ! empty( $payload ) ) {
			$parsed_args['body'] = \wp_json_encode( $payload );
		}

		$request_response = \wp_remote_request( "{$this->api}/{$path}", $parsed_args );

		// E.g. Hiive is down, or the site has disabled HTTP requests.
		if ( \is_wp_error( $request_response ) ) {
			return $request_response;
		}

		// Authentication token is valid for Hiive but not for the resource or Site.
		if ( 403 === $request_response['response']['code'] ) {
			$body = json_decode( $request_response['body'], true );
			if ( 'Invalid token for url' === $body['message'] ) {
				if ( $this->reconnect() ) {
					$this->hiive_request( $path, $payload, $args );
				} else {
					return new WP_Error( 'hiive_connection', __( 'This site is not connected to the hiive.' ) );
				}
			}
		}

		\remove_filter( 'http_headers_useragent', array( $this, 'add_plugin_name_version_to_user_agent' ) );

		return $request_response;
	}

	/**
	 * Try to return the auth token
	 *
	 * This is cleared whenever Hiive returns 401 unauthenticated {@see Data::delete_token_on_401_response()}.
	 *
	 * @return string|false The decrypted token if it's set
	 */
	public static function get_auth_token() {
		return \get_option( 'nfd_data_token' );
	}

	/**
	 * Get core site data for initial connection
	 *
	 * @return array
	 */
	public function get_core_data() {
		global $wpdb, $wp_version;
		$container = container();

		$data = array(
			'brand'       => \sanitize_title( $container->plugin()->brand ),
			'cache_level' => intval( \get_option( 'newfold_cache_level', 2 ) ),
			'cloudflare'  => \get_option( 'newfold_cloudflare_enabled', false ),
			'data'        => defined( 'NFD_DATA_MODULE_VERSION' ) ? constant( 'NFD_DATA_MODULE_VERSION' ) : '0.0',
			'email'       => \get_option( 'admin_email' ),
			'hostname'    => gethostname(),
			'mysql'       => $wpdb->db_version(),
			'origin'      => $container->plugin()->get( 'id', 'error' ),
			'php'         => phpversion(),
			'plugin'      => $container->plugin()->get( 'version', '0' ),
			'url'         => \get_site_url(),
			'username'    => get_current_user(),
			'wp'          => $wp_version,
			'server_path' => defined( 'ABSPATH' ) ? constant( 'ABSPATH' ) : '',
		);

		return apply_filters( 'newfold_wp_data_module_core_data_filter', $data );
	}

	/**
	 * Add the plugin name and version to the user agent string
	 *
	 * @param string $user_agent E.g. "WordPress/6.4.3; https://example.org".
	 * @param string $url   E.g. "https://hiive.cloud/api/sites/v2/events".
	 *
	 * @return string E.g. "WordPress/6.4.3; bluehost/1.2.3; https://example.org".
	 */
	public function add_plugin_name_version_to_user_agent( string $user_agent, string $url ): string {
		$container      = container();
		$plugin_brand   = \sanitize_title( $container->plugin()->brand );
		$plugin_version = $container->plugin()->get( 'version', '0' );

		$user_agent_parts = array_map( 'trim', explode( ';', $user_agent ) );

		array_splice( $user_agent_parts, 1, 0, "{$plugin_brand}/{$plugin_version}" );

		return implode( '; ', $user_agent_parts );
	}
}
