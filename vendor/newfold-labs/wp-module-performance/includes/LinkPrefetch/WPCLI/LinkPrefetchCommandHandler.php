<?php

namespace NewfoldLabs\WP\Module\Performance\LinkPrefetch\WPCLI;

use NewfoldLabs\WP\Module\Performance\NFD_WPCLI;
use NewfoldLabs\WP\Module\Performance\LinkPrefetch\LinkPrefetch;

/**
 * Handles WP-CLI commands for Link Prefetch settings.
 */
class LinkPrefetchCommandHandler {

	/**
	 * Allowed status values.
	 *
	 * @var array
	 */
	private const VALID_STATUSES = array( 'on', 'off' );

	/**
	 * Updates a single setting value.
	 *
	 * @param string $key   The setting key to update.
	 * @param mixed  $value The new value.
	 *
	 * @return void
	 */
	private function update_single_setting( $key, $value ) {
		$settings         = LinkPrefetch::get_settings();
		$settings[ $key ] = $value;
		LinkPrefetch::update_settings( $settings );
	}

	/**
	 * Validates that a status value is either 'on' or 'off'.
	 *
	 * @param string|null $status The status value.
	 *
	 * @return string The validated status.
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

	/**
	 * Toggles the activeOnDesktop setting.
	 *
	 * ## OPTIONS
	 *
	 * <status>
	 * : Enable or disable activeOnDesktop. Accepts 'on' or 'off'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance link_prefetch active_desktop on
	 *     wp nfd performance link_prefetch active_desktop off
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function active_desktop( $args ) {
		$status = $this->validate_status( $args[0] ?? null );
		$this->update_single_setting( 'activeOnDesktop', ( 'on' === $status ) );
		NFD_WPCLI::success(
			sprintf(
				/* translators: %s is the on/off status. */
				__( "Setting 'activeOnDesktop' has been turned %s.", 'wp-module-performance' ),
				$status
			)
		);
	}

	/**
	 * Toggles the activeOnMobile setting.
	 *
	 * ## OPTIONS
	 *
	 * <status>
	 * : Enable or disable activeOnMobile. Accepts 'on' or 'off'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance link_prefetch active_mobile on
	 *     wp nfd performance link_prefetch active_mobile off
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function active_mobile( $args ) {
		$status = $this->validate_status( $args[0] ?? null );
		$this->update_single_setting( 'activeOnMobile', ( 'on' === $status ) );
		NFD_WPCLI::success(
			sprintf(
				/* translators: %s is the on/off status. */
				__( "Setting 'activeOnMobile' has been turned %s.", 'wp-module-performance' ),
				$status
			)
		);
	}

	/**
	 * Toggles the instantClick setting.
	 *
	 * ## OPTIONS
	 *
	 * <status>
	 * : Enable or disable instantClick. Accepts 'on' or 'off'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance link_prefetch instant_click on
	 *     wp nfd performance link_prefetch instant_click off
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function instant_click( $args ) {
		$status = $this->validate_status( $args[0] ?? null );
		$this->update_single_setting( 'instantClick', ( 'on' === $status ) );
		NFD_WPCLI::success(
			sprintf(
				/* translators: %s is the on/off status. */
				__(
					"Setting 'instantClick' has been turned %s.",
					'wp-module-performance'
				),
				$status
			)
		);
	}

	/**
	 * Sets the behavior setting.
	 *
	 * ## OPTIONS
	 *
	 * <value>
	 * : The desired behavior value. (mouseHover, mouseDown)
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance link_prefetch behavior mouseHover
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function behavior( $args ) {
		if ( empty( $args[0] ) ) {
			NFD_WPCLI::error( __( 'A behavior value is required.', 'wp-module-performance' ) );
		}
		$value = $args[0];
		if ( ! in_array( $value, LinkPrefetch::VALID_BEHAVIORS, true ) ) {
			NFD_WPCLI::error(
				sprintf(
					/* translators: %s is the list of valid mobile behaviors. */
					__( 'Invalid behavior: Use one of the following - %s.', 'wp-module-performance' ),
					implode( ', ', LinkPrefetch::VALID_BEHAVIORS )
				)
			);
		}
		$this->update_single_setting( 'behavior', $value );
		NFD_WPCLI::success(
			sprintf(
				/* translators: %s is the new behavior value. */
				__( "Setting 'behavior' has been set to '%s'.", 'wp-module-performance' ),
				$value
			)
		);
	}

	/**
	 * Sets the hoverDelay setting.
	 *
	 * ## OPTIONS
	 *
	 * <value>
	 * : The numeric value for hoverDelay.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance link_prefetch hover_delay 80
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function hover_delay( $args ) {
		if ( empty( $args[0] ) || ! is_numeric( $args[0] ) ) {
			NFD_WPCLI::error( __( 'A numeric value for hoverDelay is required.', 'wp-module-performance' ) );
		}
		$value = (int) $args[0];
		$this->update_single_setting( 'hoverDelay', $value );
		NFD_WPCLI::success(
			sprintf(
				/* translators: %s is the numeric value for hoverDelay. */
				__( "Setting 'hoverDelay' has been set to '%s'.", 'wp-module-performance' ),
				$value
			)
		);
	}

	/**
	 * Sets the mobileBehavior setting.
	 *
	 * ## OPTIONS
	 *
	 * <value>
	 * : The desired mobile behavior value. (touchstart, viewport)
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance link_prefetch mobile_behavior touchstart
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function mobile_behavior( $args ) {
		if ( empty( $args[0] ) ) {
			NFD_WPCLI::error( __( 'A mobile behavior value is required.', 'wp-module-performance' ) );
		}
		$value = $args[0];
		if ( ! in_array( $value, LinkPrefetch::VALID_MOBILE_BEHAVIORS, true ) ) {
			NFD_WPCLI::error(
				sprintf(
					/* translators: %s is the list of valid mobile behaviors. */
					__( 'Invalid behavior: Use one of the following - %s.', 'wp-module-performance' ),
					implode( ', ', LinkPrefetch::VALID_MOBILE_BEHAVIORS )
				)
			);
		}
		$this->update_single_setting( 'mobileBehavior', $value );
		NFD_WPCLI::success(
			sprintf(
				/* translators: %s is the new mobile behavior value. */
				__( "Setting 'mobileBehavior' has been set to '%s'.", 'wp-module-performance' ),
				$value
			)
		);
	}

	/**
	 * Sets the ignoreKeywords setting.
	 *
	 * ## OPTIONS
	 *
	 * <value>
	 * : The desired value for ignoreKeywords.
	 *
	 * ## EXAMPLES
	 *
	 *     wp nfd performance link_prefetch ignore_keywords "#,?"
	 *
	 * @param array $args Positional arguments.
	 *
	 * @return void
	 */
	public function ignore_keywords( $args ) {
		if ( ! isset( $args[0] ) ) {
			NFD_WPCLI::error( __( 'A value for ignoreKeywords is required.', 'wp-module-performance' ) );
		}

		// Sanitize the entire input
		$value = sanitize_text_field( $args[0] );

		// Sanitize each keyword since it's a comma-separated list
		$keywords = array_map( 'trim', explode( ',', $value ) );
		$value    = implode( ',', $keywords );

		$this->update_single_setting( 'ignoreKeywords', $value );

		NFD_WPCLI::success(
			sprintf(
				/* translators: %s is the new ignoreKeywords value. */
				__( "Setting 'ignoreKeywords' has been set to '%s'.", 'wp-module-performance' ),
				$value
			)
		);
	}
}
