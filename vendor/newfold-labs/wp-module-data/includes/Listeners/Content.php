<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

use WP_Query;

/**
 * Monitors page/post events
 */
class Content extends Listener {

	/**
	 * Register the hooks for the subscriber
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Post status transitions
		add_action( 'transition_post_status', array( $this, 'post_status' ), 10, 3 );

		// transition comment status
		add_action( 'transition_comment_status', array( $this, 'comment_status' ), 10, 3 );
	}

	/**
	 * Post status transition
	 *
	 * @param string   $new_status The new post status
	 * @param string   $old_status The old post status
	 * @param \WP_Post $post Post object
	 *
	 * @return void
	 */
	public function post_status( $new_status, $old_status, $post ) {

		$post_type = get_post_type_object( $post->post_type );

		/**
		 * Ignore all post types that aren't public
		 */
		if ( ! $post_type || $post_type->public !== true ) {
			return;
		}

		$allowed_statuses = array(
			'draft',
			'pending',
			'publish',
			'new',
			'future',
			'private',
			'trash',
		);
		if ( $new_status !== $old_status && in_array( $new_status, $allowed_statuses, true ) ) {
			$data = array(
				'label_key'  => 'new_status',
				'old_status' => $old_status,
				'new_status' => $new_status,
				'post'       => $post,
			);
			$this->push( 'content_status', $data );

			if ( 'publish' === $new_status ) {
				$count = $this->count_posts();

				if ( 1 === $count ) {
					$this->push( 'first_post_published', array( 'post' => $post ) );
				}

				if ( 5 === $count ) {
					$this->push( 'fifth_post_published', array( 'post' => $post ) );
				}
			}
		}
	}

	/**
	 * Count published posts excluding the default 3: Sample Page, Hello World and the Privacy Page
	 *
	 * @return integer Number of published non-default posts
	 */
	public function count_posts() {
		$types = get_post_types( array( 'public' => true ) );
		$args  = array(
			'post_status'  => 'publish',
			'post_type'    => $types,
			'post__not_in' => array( 1, 2, 3 ),
		);
		$query = new WP_Query( $args );

		return $query->post_count;
	}

	/**
	 * Comment status transition
	 *
	 * @param string     $new_status The new comment status
	 * @param string     $old_status The new comment status
	 * @param WP_Comment $comment Comment object
	 *
	 * @return void
	 */
	public function comment_status( $new_status, $old_status, $comment ) {
		$allowed_statuses = array(
			'deleted',
			'approved',
			'unapproved',
			'spam',
		);
		if ( $new_status !== $old_status && in_array( $new_status, $allowed_statuses, true ) ) {
			$data = array(
				'label_key'  => 'new_status',
				'old_status' => $old_status,
				'new_status' => $new_status,
				'comment'    => $comment,
			);
			$this->push( 'comment_status', $data );
		}
	}
}
