<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors generic theme events
 */
class Theme extends Listener {

	/**
	 * Register the hooks for the listener
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Theme changed
		add_filter( 'pre_update_option_stylesheet', array( $this, 'theme_changed' ), 10, 2 );

		// Mojo theme preview
		add_action( 'admin_footer', array( $this, 'mojo_preview' ) );

		// Browse theme category .org
		// @todo Check when switching between categories - may need to use wp.org api filter
		add_action( 'admin_footer-theme-install.php', array( $this, 'browse_wporg_themes' ) );

		// @todo Need ajax event for Mojo themes
	}

	/**
	 * Theme changed
	 *
	 * @param string $new_option New theme
	 * @param string $old_option Old theme
	 * @return string The new theme
	 */
	public function theme_changed( $new_option, $old_option ) {
		if ( $new_option !== $old_option ) {
			$data = array(
				'label_key' => 'new_theme',
				'old_theme' => $old_option,
				'new_theme' => $new_option,
			);
			$this->push( 'theme_changed', $data );
		}

		return $new_option;
	}

	/**
	 * Preview of Mojo Marketplace theme
	 *
	 * @return void
	 */
	public function mojo_preview() {
		global $theme;
		if ( isset( $_GET['page'] ) && 'mojo-theme-preview' === $_GET['page'] && ! is_wp_error( $theme ) ) {
			$this->push(
				'mojo_theme_preview',
				array(
					'label_key' => 'theme',
					'theme'     => $theme,
				)
			);
		}
	}

	/**
	 * Browse free wordpress.org themes
	 *
	 * @return void
	 */
	public function browse_wporg_themes() {
		$category = ( isset( $_GET['browse'] ) ) ? esc_attr( $_GET['browse'] ) : 'featured';
		$this->push(
			'browse_wporg_themes',
			array(
				'label_key' => 'category',
				'category'  => $category,
			)
		);
	}
}
