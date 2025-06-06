<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors generic admin events
 */
class Admin extends Listener {

	/**
	 * Register all required hooks for the listener category
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Admin pages
		add_action( 'admin_footer', array( $this, 'view' ), 9 );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'view' ) );

		// Site URL changes
		add_action( 'update_option_siteurl', array( $this, 'site_url_change' ), 10, 2 );

		// Login
		add_action( 'wp_login', array( $this, 'login' ), 10, 2 );

		// Logout
		add_action( 'wp_logout', array( $this, 'logout' ) );
	}

	/**
	 * Default admin event
	 *
	 * @return void
	 */
	public function view() {
		global $title;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		$this->push(
			'pageview',
			array(
				'page'       => get_site_url( null, $_SERVER['REQUEST_URI'] ),
				'page_title' => $title,
			)
		);
	}

	/**
	 * Trigger event for Site URL change
	 *
	 * @param string $old_url The old url option value
	 * @param string $new_url The new url option value
	 *
	 * @return void
	 */
	public function site_url_change( $old_url, $new_url ) {
		if ( $new_url !== $old_url ) {
			$this->push(
				'site_url_change',
				array(
					'action'   => 'site_url_change',
					'category' => 'admin',
					'data'     => array(
						'label_key' => 'after',
						'after'     => $new_url,
						'before'    => $old_url,
						'page'      => get_site_url( null, $_SERVER['REQUEST_URI'] ),
					),
				)
			);
		}
	}

	/**
	 * Login
	 *
	 * @hooked wp_login
	 *
	 * @param string   $user_login username
	 * @param \WP_User $user       logged in user info
	 */
	public function login( $user_login, $user ): void {
		$is_admin = array_key_exists( 'administrator', $user->get_role_caps() );
		if ( ( $is_admin && $user->get_role_caps()['administrator'] ) || ( $user->get_role_caps() && $user->get_role_caps()['manage_options'] ) ) {
			$this->push( 'login', array( 'user_email' => $user->user_email ) );
		}
	}

	/**
	 * Logout
	 *
	 * @return void
	 */
	public function logout() {
		$this->push( 'logout' );
	}
}
