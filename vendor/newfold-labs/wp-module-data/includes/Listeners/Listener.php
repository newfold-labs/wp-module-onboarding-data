<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

use NewfoldLabs\WP\Module\Data\EventManager;
use NewfoldLabs\WP\Module\Data\Event;

/**
 * Base class for listeners
 */
abstract class Listener {

	/**
	 * Event Manager instance
	 *
	 * @var EventManager
	 */
	protected $manager;

	/**
	 * Default constructor
	 *
	 * @param EventManager $manager Instance of the Event Manager the listener is registered to
	 */
	public function __construct( EventManager $manager ) {
		$this->manager = $manager;
	}

	/**
	 * Register all required hooks for the listener category
	 */
	public function register_hooks() {}

	/**
	 * Wrapper for pushing event data up to the Event Manager
	 *
	 * @param string $key  Key representing the action that occurred
	 * @param array  $data Optional array of additional data for the action
	 * @return void
	 */
	protected function push( $key, $data = array() ) {
		$category = $this->get_class_name();
		$event    = new Event( $category, $key, $data );
		$this->manager->push( $event );
	}

	/**
	 * Return the name of the current class without the namespace
	 *
	 * @return string Name of the class or false
	 */
	protected function get_class_name() {
		$class    = get_class( $this );
		$position = strrpos( $class, '\\' );

		return substr( $class, $position + 1 );
	}
}
