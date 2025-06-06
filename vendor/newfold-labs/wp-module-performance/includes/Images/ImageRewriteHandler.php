<?php

namespace NewfoldLabs\WP\Module\Performance\Images;

use WP_Forge\WP_Htaccess_Manager\htaccess;
use function WP_Forge\WP_Htaccess_Manager\removeMarkers;

/**
 * Handles the management of .htaccess rules for optimized image redirects.
 */
class ImageRewriteHandler {

	/**
	 * Marker for missing image redirect rules.
	 */
	const MISSING_IMAGE_MARKER = 'Newfold WebP Missing Image Redirect';

	/**
	 * Marker for existing image redirect rules.
	 */
	const EXISTING_IMAGE_MARKER = 'Newfold WebP Existing Image Redirect';

	/**
	 * Constructor to set up listeners.
	 */
	public function __construct() {
		add_action( 'update_option_nfd_image_optimization', array( $this, 'on_image_setting_change' ), 10, 2 );
	}

	/**
	 * Add the missing image redirect rule to .htaccess.
	 */
	public function add_missing_image_rule() {
		$rules = array(
			'<IfModule mod_rewrite.c>',
			"\tRewriteEngine On",
			"\tRewriteCond %{REQUEST_FILENAME} !-f",
			"\tRewriteCond %{REQUEST_FILENAME} !-d",
			"\tRewriteCond %{REQUEST_URI} (.+)\\.(gif|bmp|jpg|jpeg|png|tiff|svg|webp)$ [NC]",
			"\tRewriteCond %{DOCUMENT_ROOT}%1.webp -f",
			"\tRewriteRule ^(.+)\\.(gif|bmp|jpg|jpeg|png|tiff|svg|webp)$ $1.webp [T=image/webp,E=WEBP_REDIRECT:1,L]",
			'</IfModule>',
		);

		$htaccess = new htaccess( self::MISSING_IMAGE_MARKER );
		return $htaccess->addContent( $rules );
	}

	/**
	 * Add the existing image redirect rule to .htaccess.
	 */
	public function add_existing_image_rule() {
		$rules = array(
			'<IfModule mod_rewrite.c>',
			"\tRewriteEngine On",
			"\tRewriteCond %{REQUEST_FILENAME} -f",
			"\tRewriteCond %{REQUEST_URI} (.+)\\.(gif|bmp|jpg|jpeg|png|tiff|svg|webp)$ [NC]",
			"\tRewriteCond %{DOCUMENT_ROOT}%1.webp -f",
			"\tRewriteRule ^(.+)\\.(gif|bmp|jpg|jpeg|png|tiff|svg|webp)$ $1.webp [T=image/webp,E=WEBP_REDIRECT:1,L]",
			'</IfModule>',
		);

		$htaccess = new htaccess( self::EXISTING_IMAGE_MARKER );
		return $htaccess->addContent( $rules );
	}

	/**
	 * Remove both rules from the .htaccess file.
	 */
	public function remove_rules() {
		removeMarkers( self::MISSING_IMAGE_MARKER );
		removeMarkers( self::EXISTING_IMAGE_MARKER );
	}

	/**
	 * Activate the rules when needed.
	 */
	public function on_activation() {
		$this->add_missing_image_rule();
		$this->add_existing_image_rule();
	}

	/**
	 * Deactivate the rules when needed.
	 */
	public function on_deactivation() {
		$this->remove_rules();
	}

	/**
	 * Handle changes to image optimization settings.
	 *
	 * @param array $old_value The previous settings (not used).
	 * @param array $new_value The updated settings.
	 */
	public function on_image_setting_change( $old_value, $new_value ) {
		// If the image optimization is disabled, remove all rules and return.
		if ( empty( $new_value['enabled'] ) ) {
			$this->remove_rules();
			return;
		}

		// Handle 'auto_delete_original_image' setting.
		if ( ! empty( $new_value['auto_optimized_uploaded_images']['auto_delete_original_image'] ) ) {
			$this->add_missing_image_rule();
		} else {
			removeMarkers( self::MISSING_IMAGE_MARKER );
		}

		// Handle 'prefer_optimized_image_when_exists' setting.
		if ( ! empty( $new_value['prefer_optimized_image_when_exists'] ) ) {
			$this->add_existing_image_rule();
		} else {
			removeMarkers( self::EXISTING_IMAGE_MARKER );
		}
	}
}
