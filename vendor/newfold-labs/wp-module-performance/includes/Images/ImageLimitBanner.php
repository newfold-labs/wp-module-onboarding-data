<?php

namespace NewfoldLabs\WP\Module\Performance\Images;

/**
 * Displays admin notices for rate limits and bans in the WP Admin area.
 */
class ImageLimitBanner {
	/**
	 * The active brand plugin.
	 *
	 * @var string
	 */
	private $brand;

	/**
	 * Initializes the Image Limit or Ban Banner.
	 *
	 * @param Container $container Dependency injection container.
	 */
	public function __construct( $container ) {
		$this->brand = $container->plugin()->id;
		add_action( 'admin_notices', array( $this, 'display_admin_banner' ) );
	}

	/**
	 * Displays the admin banner for rate limits or bans.
	 */
	public function display_admin_banner() {
		// Check for rate limiting.
		$rate_limit_time = get_transient( ImageService::$rate_limit_transient_key );
		if ( $rate_limit_time ) {
			$this->display_rate_limit_banner( $rate_limit_time );
			return;
		}

		// Check for permanent ban.
		$is_banned = ImageSettings::is_banned();
		if ( $is_banned ) {
			$this->display_ban_banner();
		}
	}

	/**
	 * Displays the rate limit banner.
	 *
	 * @param int $rate_limit_time Timestamp when the rate limit will expire.
	 */
	private function display_rate_limit_banner( $rate_limit_time ) {
		$retry_after = human_time_diff( time(), $rate_limit_time );

		echo '<div class="notice notice-warning is-dismissible">';
		echo '<p>';
		printf(
			/* translators: %s: Time remaining */
			esc_html__( 'This site has made too many requests in a short period. Please wait %s before trying again.', 'wp-module-performance' ),
			esc_html( $retry_after )
		);
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Displays the permanent ban banner.
	 */
	private function display_ban_banner() {
		$support_link = admin_url( "admin.php?page={$this->brand}#/help" );

		echo '<div class="notice notice-error">';
		echo '<p>';
		printf(
			wp_kses(
				/* translators: %s: Support link */
				__( 'This site no longer qualifies for image optimization as it has reached its usage limits. Please <a href="%s">contact support</a> for assistance.', 'wp-module-performance' ),
				array(
					'a' => array(
						'href' => array(),
					),
				)
			),
			esc_url( $support_link )
		);
		echo '</p>';
		echo '</div>';
	}
}
