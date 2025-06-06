<?php

namespace NewfoldLabs\WP\Module\Data;

use Exception;
use NewfoldLabs\WP\Module\Data\EventQueue\EventQueue;
use NewfoldLabs\WP\Module\Data\Listeners\Listener;
use WP_Error;

/**
 * Class to manage event subscriptions
 */
class EventManager {

	/**
	 * List of default listener category classes
	 *
	 * @var Listener[]
	 */
	const LISTENERS = array(
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Admin',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Content',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Cron',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Jetpack',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Plugin',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\BluehostPlugin',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\SiteHealth',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Theme',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Commerce',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Yoast',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\WonderCart',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\WPMail',
	);

	/**
	 * List of subscribers receiving event data
	 *
	 * @var array
	 */
	private $subscribers = array();

	/**
	 * The queue of events logged in the current request
	 *
	 * @var Event[]
	 */
	private $queue = array();

	/**
	 * Initialize the Event Manager
	 */
	public function init(): void {
		$this->initialize_listeners();
		$this->initialize_cron();

		// Register the shutdown hook which sends or saves all queued events
		add_action( 'shutdown', array( $this, 'shutdown' ) );
	}

	/**
	 * Initialize the REST API endpoint.
	 *
	 * @see Data::init()
	 */
	public function initialize_rest_endpoint() {
		// Register REST endpoint.
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	/**
	 * Handle setting up the scheduled job for sending updates
	 */
	protected function initialize_cron(): void {
		// Ensure there is a minutely option in the cron schedules
		// phpcs:disable WordPress.WP.CronInterval.CronSchedulesInterval
		add_filter( 'cron_schedules', array( $this, 'add_minutely_schedule' ) );

		// Minutely cron hook
		add_action( 'nfd_data_sync_cron', array( $this, 'send_saved_events_batch' ) );

		// Register the cron task
		if ( ! wp_next_scheduled( 'nfd_data_sync_cron' ) ) {
			wp_schedule_event( time() + constant( 'MINUTE_IN_SECONDS' ), 'minutely', 'nfd_data_sync_cron' );
		}
	}

	/**
	 * Register the event route.
	 */
	public function rest_api_init() {
		$controller = new API\Events( Data::$instance->hiive, $this );
		$controller->register_routes();
	}

	/**
	 * Add the weekly option to cron schedules if it doesn't exist
	 *
	 * @hooked cron_schedules
	 *
	 * @param  array<string, array{interval:int, display:string}> $schedules  List of defined cron schedule options.
	 *
	 * @return array<string, array{interval:int, display:string}>
	 */
	public function add_minutely_schedule( $schedules ) {
		if ( ! array_key_exists( 'minutely', $schedules ) ||
			MINUTE_IN_SECONDS !== $schedules['minutely']['interval']
			) {
			$schedules['minutely'] = array(
				'interval' => MINUTE_IN_SECONDS,
				'display'  => __( 'Once Every Minute' ),
			);
		}

		return $schedules;
	}

	/**
	 * Sends or saves all queued events at the end of the request
	 *
	 * @hooked shutdown
	 */
	public function shutdown(): void {

		// Due to a bug sending too many events, we are temporarily disabling these.
		$disabled_events = array( 'pageview', 'page_view', 'wp_mail', 'plugin_updated' );
		foreach ( $this->queue as $index => $event ) {
			if ( in_array( $event->key, $disabled_events, true ) ) {
				unset( $this->queue[ $index ] );
			}
		}

		// Separate out the async events
		$async = array();
		foreach ( $this->queue as $index => $event ) {
			if ( 'pageview' === $event->key ) {
				$async[] = $event;
				unset( $this->queue[ $index ] );
			}
		}

		// Save any async events for sending later
		if ( ! empty( $async ) ) {
			EventQueue::getInstance()->queue()->push( $async );
		}

		// Any remaining items in the queue should be sent now
		if ( ! empty( $this->queue ) ) {
			$this->send_request_events( $this->queue );
		}
	}

	/**
	 * Register a new event subscriber
	 *
	 * @param  SubscriberInterface $subscriber  Class subscribing to event updates
	 */
	public function add_subscriber( SubscriberInterface $subscriber ): void {
		$this->subscribers[] = $subscriber;
	}

	/**
	 * Returns filtered list of registered event subscribers
	 *
	 * @return array<SubscriberInterface> List of subscriber classes
	 */
	public function get_subscribers() {
		return apply_filters( 'newfold_data_subscribers', $this->subscribers );
	}

	/**
	 * Return an array of listener classes
	 *
	 * @return Listener[] List of listener classes
	 */
	public function get_listeners() {
		return apply_filters( 'newfold_data_listeners', $this::LISTENERS );
	}

	/**
	 * Initialize event listener classes
	 */
	protected function initialize_listeners(): void {
		if ( defined( 'BURST_SAFETY_MODE' ) && constant( 'BURST_SAFETY_MODE' ) ) {
			// Disable listeners when site is under heavy load
			return;
		}
		foreach ( $this->get_listeners() as $listener ) {
			$class = new $listener( $this );
			$class->register_hooks();
		}
	}

	/**
	 * Push event data onto the queue
	 *
	 * @param  Event $event  Details about the action taken
	 */
	public function push( Event $event ): void {
		/**
		 * The `nfd_event_log` action is handled in the notification module.
		 *
		 * @see wp-module-notifications/notifications.php
		 */
		do_action( 'nfd_event_log', $event->key, $event );
		$this->queue[] = $event;
	}

	/**
	 * Send queued events to all subscribers; store them if they fail
	 *
	 * @used-by EventManager::shutdown()
	 *
	 * @param  Event[] $events  A list of events
	 */
	protected function send_request_events( array $events ): void {
		foreach ( $this->get_subscribers() as $subscriber ) {
			/**
			 * @var array{succeededEvents:array,failedEvents:array}|WP_Error $response
			 */
			$response = $subscriber->notify( $events );

			// Due to an unidentified bug causing events to be resent, we are temporarily disabling retries.
			continue;

			if ( ! ( $subscriber instanceof HiiveConnection ) ) {
				continue;
			}

			if ( is_wp_error( $response ) ) {
				EventQueue::getInstance()->queue()->push( $events );
				continue;
			}

			if ( ! empty( $response['failedEvents'] ) ) {
				EventQueue::getInstance()->queue()->push( $response['failedEvents'] );
			}
		}
	}

	/**
	 * Send stored events to all subscribers; remove/release them from the store aftewards.
	 *
	 * @hooked nfd_data_sync_cron
	 */
	public function send_saved_events_batch(): void {

		$queue = EventQueue::getInstance()->queue();

		/**
		 * Array indexed by the table row id.
		 *
		 * @var array<int,Event> $events
		 */
		$events = $queue->pull( 50 );

		// If queue is empty, do nothing.
		if ( empty( $events ) ) {
			return;
		}

		// Reserve the events in the queue so they are not processed by another instance.
		if ( ! $queue->reserve( array_keys( $events ) ) ) {
			// If the events fail to reserve, they will be repeatedly retried.
			// It would be good to log this somewhere.
			return;
		}

		foreach ( $this->get_subscribers() as $subscriber ) {
			/**
			 * @var array{succeededEvents:array,failedEvents:array}|WP_Error $response
			 */
			$response = $subscriber->notify( $events );

			// Due to an unidentified bug causing events to be resent, we are temporarily disabling retries.
			continue;

			if ( ! ( $subscriber instanceof HiiveConnection ) ) {
				continue;
			}

			if ( is_wp_error( $response ) ) {
				$queue->release( array_keys( $events ) );
				continue;
			}

			// Remove from the queue.
			if ( ! empty( $response['succeededEvents'] ) ) {
				$queue->remove( array_keys( $response['succeededEvents'] ) );
			}

			// Release the 'reserve' we placed on the entry, so it will be tried again later.
			if ( ! empty( $response['failedEvents'] ) ) {
				$queue->release( array_keys( $response['failedEvents'] ) );
			}
		}

		// Due to an unidentified bug causing events to be resent, we are temporarily disabling retries.
		$queue->remove( array_keys( $events ) );
	}
}
