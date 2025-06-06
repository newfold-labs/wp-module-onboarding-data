<?php

namespace NewfoldLabs\WP\Module\Data;

/**
 * Subscriber interface for registering to receive event notifications
 */
interface SubscriberInterface {

	/**
	 * Method for handling receiving event data
	 *
	 * @param array $events Array of Event objects representing data about the events that occurred
	 */
	public function notify( $events );
}
