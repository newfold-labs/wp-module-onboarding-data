<?php

namespace NewfoldLabs\WP\Module\Features;

/**
 * Class for Features CLI commands
 */
class FeaturesCLI extends \WP_CLI_Command {

	/**
	 * Used internally to interface with features.
	 *
	 * @param array $args       Command arguments
	 * @param array $assoc_args Associative command arguments.
	 *
	 * @throws \WP_CLI\ExitException On CLI failure.
	 */
	public function __invoke( $args, $assoc_args ) {

		if ( ! is_array( $args ) || ! isset( $args[0] ) ) {
			$this->error( 'No sub-command provided' );
		}

		switch ( $args[0] ) {

			case 'list':
				$this->list();
				break;

			case 'isEnabled':
				$this->isEnabled( $args[1] );
				break;

			case 'enable':
				$this->enable( $args[1] );
				break;

			case 'disable':
				$this->disable( $args[1] );
				break;

			default:
				$this->error( 'Invalid action' );
		}
	}

	/**
	 * List command - lists features
	 */
	protected function list() {
		$features = Features::getInstance()->getFeatureList();
		$response = array(
			'status'  => 'success',
			'message' => implode( ',', $features ),
		);
		$this->render( $response );
	}

	/**
	 * Is Enabled command - checks if feature is enabled
	 *
	 * @param string $name the feature name.
	 */
	protected function isEnabled( $name ) {
		$feature = Features::getInstance()->getFeature( $name );
		if ( $feature ) {
			$response = array(
				'status'  => 'success',
				'message' => $feature->isEnabled() ? 'true' : 'false',
			);
		} else {
			$response = array(
				'status'  => 'error',
				'message' => __( 'Invalid feature name: ', 'wp-module-features' ) . $name,
			);
		}
		$this->render( $response );
	}

	/**
	 * Enable command - enables a feature
	 *
	 * @param string $name the feature name.
	 */
	protected function enable( $name ) {
		$feature = Features::getInstance()->getFeature( $name );
		if ( $feature ) {
			$response = array(
				'status'  => 'success',
				'message' => $feature->enable(),
			);
		} else {
			$response = array(
				'status'  => 'error',
				'message' => __( 'Invalid feature name: ', 'wp-module-features' ) . $name,
			);
		}
		$this->render( $response );
	}

	/**
	 * Disable command - disables a feature
	 *
	 * @param string $name the feature name.
	 */
	protected function disable( $name ) {
		$feature = Features::getInstance()->getFeature( $name );
		if ( $feature ) {
			$response = array(
				'status'  => 'success',
				'message' => $feature->disable(),
			);
		} else {
			$response = array(
				'status'  => 'error',
				'message' => __( 'Invalid feature name: ', 'wp-module-features' ) . $name,
			);
		}
		$this->render( $response );
	}

	/**
	 * Render a success or error message based on provided data.
	 *
	 * @param mixed $data The data from which to fetch the message.
	 */
	protected function render( $data ) {
		$response = array(
			'status'  => 'error',
			'message' => __( 'Invalid JSON response', 'wp-module-features' ),
		);

		switch ( gettype( $data ) ) {
			case 'boolean':
				$response = array(
					'status'  => 'success',
					'message' => __( 'Invalid JSON response', 'wp-module-features' ),
				);
				break;
			case 'string':
				$decoded = json_decode( $data );
				if ( $decoded && isset( $decoded['message'] ) ) {
					$response = $decoded;
				}
				break;
			case 'array':
				$response = $data;
				break;
			case 'object':
				if ( is_wp_error( $data ) ) {
					$response['message'] = $data->get_error_message();
				}
				break;
		}

		if ( 'success' === $response['status'] ) {
			$this->success( $response['message'] );
		} else {
			$this->error( $response['message'] );
		}
	}

	/**
	 * Helper to format data into tables.
	 *
	 * By default, the method creates simple $key => $value tables.
	 * Set $type to 'adv' and the table inherits keys from $data. DATA MUST BE UNIFORM & MATCH FIRST ROW.
	 *
	 * 1. Provide $data as an array or object
	 * 2. Provide $keys as two strings -- by default 'DETAIL' and 'VALUE' are used.
	 * 3. Prints ASCII Table
	 *
	 * @param array  $data the data
	 * @param array  $keys the keys
	 * @param string $type table type
	 */
	protected function table( $data, $keys = array( 'DETAIL', 'VALUE' ), $type = 'simple' ) {
		if ( empty( $data ) ) {
			return;
		}

		if ( 'adv' === $type ) {
			$items = $data;
			$keys  = array_keys( array_shift( $data ) );
		} else {
			$items = array();
			foreach ( $data as $detail => $value ) {
				$items[] = array(
					$keys[0] => $detail,
					$keys[1] => $value,
				);
			}
		}

		Utils\format_items( 'table', $items, $keys );
	}

	/**
	 * Creates Heading with Blue background and Grey text.
	 *
	 * @param string $message the message
	 * @param string $emoji optional emoji
	 */
	protected function bold_heading( $message, $emoji = '' ) {
		$this->colorize_log( $message, '4', 'W', $emoji );
	}

	/**
	 * Formatted Success message.
	 *
	 * @param string $message the message
	 * @param bool   $silent if the success should be silent
	 */
	protected function success( $message, $silent = false ) {
		$pre_ = $silent ? '' : 'Success: ';
		$this->colorize_log( $pre_ . $message, '2', 'k', '✅' );
	}

	/**
	 * Formatted Info message.
	 *
	 * @param string $message the message
	 */
	protected function info( $message ) {
		$this->colorize_log( $message, '4', 'W', 'ℹ️' );
	}

	/**
	 * Formatted Warning message.
	 *
	 * @param string $message the message
	 */
	protected function warning( $message ) {
		$this->colorize_log( $message, '3', 'k', '⚠️' );
	}

	/**
	 * Formatted Error message. Halts by default.
	 *
	 * @param string $message the message
	 * @param bool   $silent if the error should be silent
	 * @param bool   $halt if the error should stop execution
	 * @param int    $code error code
	 *
	 * @throws \WP_CLI\ExitException Exception.
	 */
	protected function error( $message, $silent = false, $halt = true, $code = 400 ) {
		$pre_ = $silent ? '' : 'Error: ';
		$this->colorize_log( $pre_ . $message, '1', 'W', '🛑️' );
		if ( $halt ) {
			\WP_CLI::halt( $code );
		}
	}

	/**
	 * Formatting helper for colorized messages.
	 *
	 * @param string $message the message
	 * @param string $background background color
	 * @param string $text_color text color
	 * @param string $emoji_prefix emojoi prefix
	 */
	protected function colorize_log( $message = '', $background = '', $text_color = '%_', $emoji_prefix = '' ) {
		if ( ! empty( $background ) ) {
			$background = '%' . $background;
		}

		if ( ! empty( $text_color ) && false === strpos( $text_color, '%' ) ) {
			$text_color = '%' . $text_color;
		}

		if ( ! empty( $emoji_prefix ) ) {
			$message = $emoji_prefix . '  ' . $message;
		}

		\WP_CLI::log( \WP_CLI::colorize( $background . $text_color . $message . '%n' ) );
	}

	/**
	 * Empty linebreak
	 */
	protected function new_line() {
		\WP_CLI::log( __return_empty_string() );
	}

	/**
	 * Helper function for returning clean JSON response.
	 *
	 * @param array|string $data - Provide well-formed array or existing JSON string.
	 */
	protected function log_to_json( $data ) {
		if ( is_array( $data ) ) {
			\WP_CLI::log( wp_json_encode( $data ) );
		} elseif ( is_array( json_decode( $data, true ) ) ) {
			\WP_CLI::log( $data );
		} else {
			$this->error( 'Provided $data wasn\'t valid array or JSON string.' );
		}
	}

	/**
	 * Formatted Confirm Dialog. A 'n' response breaks the thread.
	 *
	 * @param string $question the question
	 * @param string $type level
	 *
	 * @throws \WP_CLI\ExitException Exception.
	 */
	protected function confirm( $question, $type = 'normal' ) {
		switch ( $type ) {
			case 'omg':
				\WP_CLI::confirm( $this->warning( '☢ 🙊 🙈 🙊 ☢️  ' . $question ) );
				break;
			case 'red':
				\WP_CLI::confirm( $this->error( $question, true ) );
				break;
			case 'yellow':
				\WP_CLI::confirm( $this->warning( $question ) );
				break;
			case 'green':
				\WP_CLI::confirm( $this->success( $question ) );
				break;
			case 'underline':
				\WP_CLI::confirm( $this->colorize_log( $question, '', 'U' ) );
				break;
			case 'normal':
			default:
				\WP_CLI::confirm( $question );
				break;
		}
	}
}
