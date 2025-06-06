<?php
namespace NewfoldLabs\WP\Module\Performance\Data;

/**
 * Contains data related to performance Hiive events.
 */
final class Events {
	/**
	 * The Array of categories in an event.
	 *
	 * @var array
	 */
	protected static $category = array( 'nfd_performance' );

	/**
	 * List of valid actions that an event can perform.
	 *
	 * A value of true indicates that the action is valid, set it to null if you want to invalidate an action.
	 *
	 * @var array
	 */
	protected static $valid_actions = array(
		'image_transformation_requested' => true,
		'image_transformation_completed' => true,
		'image_transformation_failed'    => true,
		'image_resized'                  => true,
	);

	/**
	 * Returns the list of valid actions that an event can perform
	 *
	 * @return array
	 */
	public static function get_valid_actions() {
		return self::$valid_actions;
	}

	/**
	 * Valid categories of on event.
	 *
	 * @return array
	 */
	public static function get_category() {
		return self::$category;
	}
}
