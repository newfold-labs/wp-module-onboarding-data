<?php

namespace NewfoldLabs\WP\Module\Data\EventQueue\Queues;

use NewfoldLabs\WP\Module\Data\Event;

interface BatchQueueInterface {

	/**
	 * Push one or more events onto the queue
	 *
	 * @param  Event[] $events
	 *
	 * @return bool
	 */
	public function push( array $events );

	/**
	 * Retrieve one or more events from the queue
	 *
	 * @return Event[]
	 */
	public function pull( int $count );

	/**
	 * Remove one or more events from the queue
	 *
	 * @param  Event[] $events
	 *
	 * @return bool
	 */
	public function remove( array $events );

	/**
	 * Reserve one or more events in the queue
	 *
	 * @param  Event[] $events
	 *
	 * @return bool
	 */
	public function reserve( array $events );

	/**
	 * Release one or more events back onto the queue
	 *
	 * @param  Event[] $events
	 *
	 * @return bool
	 */
	public function release( array $events );

	/**
	 * Count the number of events in the queue
	 *
	 * @return int
	 */
	public function count();
}
