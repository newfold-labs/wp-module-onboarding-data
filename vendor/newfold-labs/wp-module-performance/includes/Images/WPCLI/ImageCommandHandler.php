<?php

namespace NewfoldLabs\WP\Module\Performance\Images\WPCLI;

use NewfoldLabs\WP\Module\Performance\NFD_WPCLI;
use NewfoldLabs\WP\Module\Performance\Images\ImageSettings;

/**
 * Handles WP-CLI commands for Image Optimization settings.
 */
class ImageCommandHandler {

	/**
	 * Allowed status values.
	 *
	 * @var array
	 */
	private const VALID_STATUSES = array( 'on', 'off' );

	/**
	 * Toggles the bulk optimization setting.
	 *
	 * ## OPTIONS
	 *
	 * <status>
	 * : Enable or disable bulk optimization. Accepts 'on' or 'off'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance images bulk_optimization on
	 *     wp nfd performance images bulk_optimization off
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function bulk_optimization( $args ) {
		$status = $this->validate_status( isset( $args[0] ) ? $args[0] : null );
		$this->toggle_setting( 'bulk_optimization', $status );
	}

	/**
	 * Toggles lazy loading.
	 *
	 * ## OPTIONS
	 *
	 * <status>
	 * : Enable or disable lazy loading. Accepts 'on' or 'off'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance images lazy_loading on
	 *     wp nfd performance images lazy_loading off
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function lazy_loading( $args ) {
		$status = $this->validate_status( isset( $args[0] ) ? $args[0] : null );
		$this->toggle_setting( 'lazy_loading.enabled', $status );
	}

	/**
	 * Toggles automatic optimization of uploaded images.
	 *
	 * ## OPTIONS
	 *
	 * <status>
	 * : Enable or disable auto-optimization. Accepts 'on' or 'off'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance images auto_optimize on
	 *     wp nfd performance images auto_optimize off
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function auto_optimize( $args ) {
		$status = $this->validate_status( isset( $args[0] ) ? $args[0] : null );
		$this->toggle_setting( 'auto_optimized_uploaded_images.enabled', $status );
	}

	/**
	 * Toggles automatic deletion of the original image.
	 *
	 * ## OPTIONS
	 *
	 * <status>
	 * : Enable or disable auto-delete of original images. Accepts 'on' or 'off'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance images auto_delete on
	 *     wp nfd performance images auto_delete off
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function auto_delete( $args ) {
		$status = $this->validate_status( isset( $args[0] ) ? $args[0] : null );
		$this->toggle_setting( 'auto_optimized_uploaded_images.auto_delete_original_image', $status );
	}

	/**
	 * Toggles WebP preference when optimized images exist.
	 *
	 * ## OPTIONS
	 *
	 * <status>
	 * : Enable or disable WebP preference. Accepts 'on' or 'off'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance images webp_preference on
	 *     wp nfd performance images webp_preference off
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function webp_preference( $args ) {
		$status = $this->validate_status( isset( $args[0] ) ? $args[0] : null );
		$this->toggle_setting( 'prefer_optimized_image_when_exists', $status );
	}

	/**
	 * Toggles all image optimization settings at once.
	 *
	 * ## OPTIONS
	 *
	 * <status>
	 * : Enable or disable all image optimization settings. Accepts 'on' or 'off'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance images all on
	 *     wp nfd performance images all off
	 *
	 * @alias all
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function all( $args ) {
		$status   = $this->validate_status( isset( $args[0] ) ? $args[0] : null );
		$enabled  = ( 'on' === $status );
		$settings = ImageSettings::get();
		$this->set_all_values( $settings, $enabled );
		ImageSettings::update( $settings );
		/* translators: %s is the on/off status. */
		NFD_WPCLI::success( sprintf( __( 'All image optimization settings have been turned %s.', 'wp-module-performance' ), $status ) );
	}

	/**
	 * Toggles a specific image optimization setting.
	 *
	 * @param string $setting The setting key (use dot notation for nested values).
	 * @param string $status  The status ('on' or 'off').
	 *
	 * @return void
	 */
	private function toggle_setting( $setting, $status ) {
		$enabled  = ( 'on' === $status );
		$settings = ImageSettings::get();

		// If a feature is turned on (except for the "all" command), enable overall image optimization.
		if ( $enabled ) {
			$settings['enabled'] = true;
		}

		// Update the specified setting.
		$this->set_nested_value( $settings, $setting, $enabled );

		// If both auto_optimize and bulk_optimization are turned off,
		// then force auto_delete (auto_delete_original_image) to be off.
		$auto_optimize     = ! empty( $settings['auto_optimized_uploaded_images']['enabled'] );
		$bulk_optimization = ! empty( $settings['bulk_optimization'] );
		if ( ! $auto_optimize && ! $bulk_optimization ) {
			$settings['auto_optimized_uploaded_images']['auto_delete_original_image'] = false;
		}

		ImageSettings::update( $settings );

		NFD_WPCLI::success(
			sprintf(
				/* translators: 1: the setting key, 2: the on/off status */
				__( "Setting '%1\$s' has been turned %2\$s.", 'wp-module-performance' ),
				$setting,
				$status
			)
		);
	}


	/**
	 * Updates a nested setting value using dot notation.
	 *
	 * @param array  $settings The settings array.
	 * @param string $key      The nested key in dot notation.
	 * @param mixed  $value    The new value to set.
	 *
	 * @return void
	 */
	private function set_nested_value( &$settings, $key, $value ) {
		$keys = explode( '.', $key );
		$temp = &$settings;
		foreach ( $keys as $part ) {
			if ( ! isset( $temp[ $part ] ) || ! is_array( $temp[ $part ] ) ) {
				$temp[ $part ] = array();
			}
			$temp = &$temp[ $part ];
		}
		$temp = $value;
	}

	/**
	 * Recursively updates all boolean settings within an array.
	 *
	 * @param array $settings The settings array to update.
	 * @param bool  $enabled  The boolean value to set.
	 *
	 * @return void
	 */
	private function set_all_values( &$settings, $enabled ) {
		foreach ( $settings as $key => &$value ) {
			if ( is_array( $value ) ) {
				$this->set_all_values( $value, $enabled );
			} else {
				$value = $enabled;
			}
		}
	}

	/**
	 * Validates and normalizes the status input.
	 *
	 * @param string|null $status The input status.
	 * @return string The normalized status.
	 */
	private function validate_status( $status ) {
		if ( empty( $status ) ) {
			NFD_WPCLI::error( __( "A status ('on' or 'off') is required.", 'wp-module-performance' ) );
		}
		$status = strtolower( $status );
		if ( ! in_array( $status, self::VALID_STATUSES, true ) ) {
			NFD_WPCLI::error( __( "Invalid status: Use 'on' or 'off'.", 'wp-module-performance' ) );
		}
		return $status;
	}
}
