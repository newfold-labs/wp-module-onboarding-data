<?php

namespace NewfoldLabs\WP\Module\Data;

use wpscholar\Url;
use function WP_Forge\Helpers\dataGet;

/**
 * Main class for the data plugin module
 */
class Data {

	/**
	 * Hiive Connection instance
	 *
	 * @var HiiveConnection
	 */
	public $hiive;

	/**
	 * Last instantiated instance of this class.
	 *
	 * @var Data
	 */
	public static $instance;

	/**
	 * Data constructor.
	 */
	public function __construct() {
		self::$instance = $this;
	}

	/**
	 * Start up the plugin module
	 *
	 * Do this separately so it isn't tied to class creation
	 *
	 * @see bootstrap.php
	 * @see \NewfoldLabs\WP\ModuleLoader\register()
	 */
	public function start(): void {

		// Delays our primary module setup until init
		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'rest_authentication_errors', array( $this, 'authenticate' ) );

		// If we ever get a 401 response from the Hiive API, delete the token.
		add_filter( 'http_response', array( $this, 'delete_token_on_401_response' ), 10, 3 );
	}

	/**
	 * Initialize all other module functionality
	 *
	 * @hooked init
	 */
	public function init(): void {

		$this->hiive = new HiiveConnection();

		$manager = new EventManager();
		$manager->initialize_rest_endpoint();

		// Initialize the required verification endpoints
		$this->hiive->register_verification_hooks();

		// If not connected, attempt to connect and
		// bail before registering the subscribers/listeners
		if ( ! $this->hiive::is_connected() ) {

			// Attempt to connect
			$this->hiive->connect();

			return;
		}

		$manager->init();

		$manager->add_subscriber( $this->hiive );

		if ( defined( 'NFD_DATA_DEBUG' ) && NFD_DATA_DEBUG ) {
			$this->logger = new Logger();
			$manager->add_subscriber( $this->logger );
		}
	}

	/**
	 * Check HTTP responses for 401 authentication errors from Hiive, delete the invalid token.
	 *
	 * @hooked http_response
	 * @see WP_Http::request()
	 *
	 * @param array  $response The successful HTTP response.
	 * @param array  $args HTTP request arguments.
	 * @param string $url The request URL.
	 *
	 * @return array
	 */
	public function delete_token_on_401_response( array $response, array $args, string $url ): array {

		if ( strpos( $url, constant( 'NFD_HIIVE_URL' ) ) === 0 && absint( wp_remote_retrieve_response_code( $response ) ) === 401 ) {
			delete_option( 'nfd_data_token' );
		}

		return $response;
	}

	/**
	 * Authenticate incoming REST API requests.
	 *
	 * @hooked rest_authentication_errors
	 *
	 * @param  bool|null|\WP_Error $errors
	 *
	 * @return bool|null|\WP_Error
	 * @see WP_REST_Server::check_authentication()
	 *
	 * @used-by ConnectSite::verifyToken() in Hiive.
	 */
	public function authenticate( $errors ) {

		// Make sure there wasn't a different authentication method used before this
		if ( ! is_null( $errors ) ) {
			return $errors;
		}

		// Make sure this is a REST API request
		if ( ! defined( 'REST_REQUEST' ) || ! constant( 'REST_REQUEST' ) ) {
			return $errors;
		}

		// If no auth header included, bail to allow a different auth method
		if ( empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			return null;
		}

		$token = str_replace( 'Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] );

		$data = array(
			'method'    => $_SERVER['REQUEST_METHOD'],
			'url'       => Url::getCurrentUrl(),
			'body'      => file_get_contents( 'php://input' ),
			'timestamp' => dataGet( getallheaders(), 'X-Timestamp' ),
		);

		$hash = hash( 'sha256', wp_json_encode( $data ) );
		$salt = hash( 'sha256', strrev( HiiveConnection::get_auth_token() ) );

		$is_valid = hash( 'sha256', $hash . $salt ) === $token;

		// Allow access if token is valid
		if ( $is_valid ) {

			if ( isset( $_GET['user_id'] ) ) {

				// If a user ID is provided, use it to find the desired user.
				$user = get_user_by( 'id', filter_input( INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT ) );

			} else {

				// If no user ID is provided, find the first admin user.
				$admins = get_users( array( 'role' => 'administrator' ) );
				$user   = array_shift( $admins );

			}

			if ( ! empty( $user ) && is_a( $user, \WP_User::class ) ) {
				wp_set_current_user( $user->ID );

				return true;
			}
		}

		// Don't return false, since we could be interfering with a basic auth implementation.
		return $errors;
	}
}
