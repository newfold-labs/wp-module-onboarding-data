<?php

namespace NewfoldLabs\WP\Module\InstallChecker;

class InstallChecker {


	/**
	 * Check if this is a fresh WordPress installation.
	 *
	 * @return bool
	 */
	public function isFreshInstallation() {

		$oldestPost = $this->getOldestPost();
		$newestPost = $this->getNewestPost();

		// If the "Hello World!" post doesn't exist, this isn't a fresh installation.
		if ( $oldestPost->ID !== 1 ) {
			return false;
		}

		// If the oldest and newest posts don't have the same modification dates, this isn't a fresh installation.
		if ( $oldestPost->post_modified_gmt !== $newestPost->post_modified_gmt ) {
			return false;
		}

		// If there isn't a user with an ID of 1, this isn't a fresh installation.
		if ( $this->getOldestUser()->ID !== 1 ) {
			return false;
		}

		// If there is more than 1 user, this isn't a fresh installation.
		if ( $this->getUserCount() !== 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the site's creation/installation date.
	 *
	 * @return int
	 */
	public function getInstallationDate() {

		$postTimestamp = get_post_timestamp( $this->getOldestPost()->ID );
		$userTimestamp = strtotime( $this->getOldestUser()->user_registered );

		return min( $postTimestamp, $userTimestamp );
	}

	/**
	 * Returns the oldest user on the site, based on user ID.
	 *
	 * @return \WP_User
	 */
	protected function getOldestUser() {
		$query = new \WP_User_Query(
			[
				'number'        => 1,
				'order'         => 'ASC',
				'orderby'       => 'ID',
				'count_total'   => true,
				'cache_results' => false,
			]
		);

		return $query->get_results()[0];
	}

	/**
	 * Returns the number of users on the site.
	 *
	 * @return int
	 */
	protected function getUserCount() {
		$query = new \WP_User_Query(
			[
				'order'         => 'ASC',
				'orderby'       => 'ID',
				'fields'        => 'ID',
				'count_total'   => true,
				'cache_results' => false,
			]
		);

		return $query->total_users;
	}

	/**
	 * Returns the oldest post on the site, based on post ID.
	 *
	 * @return \WP_Post
	 */
	protected function getOldestPost() {

		$args = [
			'post_type'           => [ 'post', 'page' ],
			'post_status'         => 'any',
			'orderby'             => 'ID',
			'order'               => 'ASC',
			'posts_per_page'      => 1,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'cache_results'       => false,
			'suppress_filters'    => true,
		];

		if ( WooCommerce::isWooCommerce() ) {
			$args['post__not_in'] = WooCommerce::getAllPageIds();
		}

		$query = new \WP_Query( $args );

		return $query->post;
	}

	/**
	 * Returns the newest post on the site, based on post ID.
	 *
	 * @return \WP_Post
	 */
	protected function getNewestPost() {

		$args = [
			'post_type'           => [ 'post', 'page' ],
			'post_status'         => 'any',
			'orderby'             => 'ID',
			'order'               => 'DESC',
			'posts_per_page'      => 1,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'cache_results'       => false,
			'suppress_filters'    => true,
		];

		if ( WooCommerce::isWooCommerce() ) {
			$args['post__not_in'] = WooCommerce::getAllPageIds();
		}

		$query = new \WP_Query( $args );

		return $query->post;
	}

}