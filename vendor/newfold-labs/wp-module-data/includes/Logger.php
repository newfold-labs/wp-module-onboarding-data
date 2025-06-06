<?php

namespace NewfoldLabs\WP\Module\Data;

/**
 * Subscribes to the Event Manager and writes event data to a log file
 */
class Logger implements SubscriberInterface {

	/**
	 * Method for handling receiving event data
	 *
	 * @param array $events Array of Event objects representing data about the events that occurred
	 */
	public function notify( $events ) {
		foreach ( $events as $event ) {
			if ( ! $event ) {
				return;
			}
			$log = wp_json_encode( $event, JSON_UNESCAPED_SLASHES ) . "\n";
			file_put_contents( dirname( __DIR__ ) . '/debug.log', $log, FILE_APPEND );
		}
	}
}
