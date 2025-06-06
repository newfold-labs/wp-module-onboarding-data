<?php

namespace NewfoldLabs\WP\Module\Data\EventQueue;

use NewfoldLabs\WP\Module\Data\EventQueue\Queues\BatchQueue;
use NewfoldLabs\WP\ModuleLoader\Container;
use WP_Forge\QueryBuilder\Query;

class EventQueue {

	/**
	 * Dependency injection container
	 *
	 * @var Container $container
	 */
	protected $container;

	/**
	 * @var EventQueue
	 */
	private static $instance;

	/**
	 * Get the singleton instance
	 *
	 * @return EventQueue
	 */
	public static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @param  Container $container
	 */
	private function __construct() {

		global $wpdb;

		$container = new Container(
			array(
				'table' => "{$wpdb->prefix}nfd_data_event_queue",
			)
		);

		$container->set(
			'query',
			$container->factory(
				function () {
					return new Query();
				}
			)
		);

		$container->set(
			'queue',
			$container->factory(
				function () use ( $container ) {
					return new Queues\BatchQueue( $container );
				}
			)
		);

		$this->container = $container;
	}

	/**
	 * Get the dependency injection container
	 *
	 * @return Container
	 */
	public function container() {
		return $this->container;
	}

	/**
	 * Get a queue instance
	 *
	 * @return BatchQueue
	 */
	public function queue() {
		return $this->container->get( 'queue' );
	}
}
