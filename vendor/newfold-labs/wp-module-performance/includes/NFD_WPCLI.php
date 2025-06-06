<?php

namespace NewfoldLabs\WP\Module\Performance;

use WP_CLI;

/**
 * Manages all "nfd" WP-CLI commands.
 */
class NFD_WPCLI {

	/**
	 * Holds registered commands.
	 *
	 * Array structure:
	 * array(
	 *     'namespace' => array(
	 *         'command' => 'handler',
	 *         // ...
	 *     ),
	 *     // ...
	 * )
	 *
	 * @var array
	 */
	private static $commands = array();

	/**
	 * Constructor. Hooks into cli_init.
	 */
	public function __construct() {
		add_action( 'cli_init', array( $this, 'register_commands' ) );
	}

	/**
	 * Validates that a given value is a non-empty string.
	 *
	 * @param mixed  $value The value to validate.
	 * @param string $field The field name (used for error messages).
	 *
	 * @return true|\WP_Error True if valid, or WP_Error on failure.
	 */
	private static function validate_required_string( $value, $field ) {
		if ( ! is_string( $value ) || empty( $value ) ) {
			return new \WP_Error(
				'nfd_cli_error',
				sprintf(
					/* translators: %s is the name of the field that is invalid (e.g., 'namespace', 'command', or 'handler'). */
					__( 'Newfold CLI: Invalid %s provided to NFD_WPCLI::add_command().', 'wp-module-performance' ),
					$field
				)
			);
		}
		return true;
	}

	/**
	 * Registers a WP-CLI command under "wp nfd".
	 *
	 * @param string $cmd_namespace The command namespace (e.g., "performance").
	 * @param string $command       The command string.
	 * @param string $handler       The handler class that implements the command.
	 *
	 * @return true|\WP_Error True on success, or WP_Error on failure.
	 */
	public static function add_command( $cmd_namespace, $command, $handler ) {
		$error = self::validate_required_string( $cmd_namespace, 'namespace' );
		if ( is_wp_error( $error ) ) {
			return $error;
		}

		$error = self::validate_required_string( $command, 'command' );
		if ( is_wp_error( $error ) ) {
			return $error;
		}

		$error = self::validate_required_string( $handler, 'handler' );
		if ( is_wp_error( $error ) ) {
			return $error;
		}

		if ( ! class_exists( $handler ) ) {
			return new \WP_Error(
				'nfd_cli_error',
				sprintf(
					/* translators: %s is the name of the handler class. */
					__( 'Newfold CLI: The handler class %s does not exist.', 'wp-module-performance' ),
					$handler
				)
			);
		}

		if ( ! isset( self::$commands[ $cmd_namespace ] ) ) {
			self::$commands[ $cmd_namespace ] = array();
		}

		self::$commands[ $cmd_namespace ][ $command ] = $handler;

		return true;
	}

	/**
	 * Hooks into WP-CLI and registers all commands.
	 *
	 * @return void
	 */
	public function register_commands() {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		foreach ( self::$commands as $cmd_namespace => $commands ) {
			foreach ( $commands as $command => $handler ) {
				WP_CLI::add_command( "nfd {$cmd_namespace} {$command}", $handler );
			}
		}
	}

	/**
	 * Outputs a warning message with "Newfold CLI:" prefix.
	 *
	 * @param string $message Message to output.
	 *
	 * @return void
	 */
	public static function warning( $message ) {
		WP_CLI::warning( 'Newfold CLI: ' . $message );
	}

	/**
	 * Outputs an error message with "Newfold CLI:" prefix.
	 *
	 * @param string $message Message to output.
	 *
	 * @return void
	 */
	public static function error( $message ) {
		WP_CLI::error( 'Newfold CLI: ' . $message );
	}

	/**
	 * Outputs a success message with "Newfold CLI:" prefix.
	 *
	 * @param string $message Message to output.
	 *
	 * @return void
	 */
	public static function success( $message ) {
		WP_CLI::success( 'Newfold CLI: ' . $message );
	}

	/**
	 * Checks if the current execution context is WP-CLI.
	 *
	 * @return bool True if running inside WP-CLI, false otherwise.
	 */
	public static function is_executing_wp_cli() {
		return defined( 'WP_CLI' ) && WP_CLI;
	}
}
