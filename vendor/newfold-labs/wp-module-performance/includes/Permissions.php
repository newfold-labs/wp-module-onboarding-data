<?php

namespace NewfoldLabs\WP\Module\Performance;

/**
 * Permissions and Authorization utility class.
 */
final class Permissions {

	/**
	 * WordPress Admin capability string
	 */
	const ADMIN                = 'manage_options';
	const UPLOAD_FILES         = 'upload_files';
	const EDIT_POSTS           = 'edit_posts';
	const MANAGE_MEDIA_LIBRARY = 'manage_media_library';

	/**
	 * Checks if the REST API caller has admin capabilities.
	 *
	 * @return bool
	 */
	public static function rest_is_authorized_admin() {
		return \is_user_logged_in() && \current_user_can( self::ADMIN );
	}

	/**
	 * Checks if the current user is logged in and is in the wp-admin with admin capabilities.
	 *
	 * @return bool
	 */
	public static function is_authorized_admin() {
		return \is_admin() && self::rest_is_authorized_admin();
	}

	/**
	 * Checks if the current user has media upload permissions.
	 *
	 * @return bool
	 */
	public static function rest_can_upload_media() {
		return \is_user_logged_in() && \current_user_can( self::UPLOAD_FILES );
	}

	/**
	 * Checks if the current user has permissions to manage media.
	 *
	 * @return bool
	 */
	public static function can_manage_media_library() {
		return \current_user_can( self::MANAGE_MEDIA_LIBRARY );
	}

	/**
	 * Checks if the user has permissions to edit posts.
	 *
	 * @return bool
	 */
	public static function can_edit_posts() {
		return \current_user_can( self::EDIT_POSTS );
	}

	/**
	 * Validates permissions for optimizing images through REST API.
	 *
	 * @return bool
	 */
	public static function rest_can_optimize_images() {
		return self::rest_can_upload_media() && self::can_manage_media_library();
	}
}
