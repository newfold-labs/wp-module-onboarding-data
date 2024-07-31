<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

/**
 * Contains functionality for managing a site's pages.
 */
class SitePagesService {
	/**
	 * Publish a new site page.
	 *
	 * @param string        $title The title of the page.
	 * @param string        $content The content(block grammar/text) that will be displayed on the page.
	 * @param boolean       $is_template_no_title checks for title
	 * @param boolean|array $meta The page post_meta.
	 * @param string        $slug The page slug that will be used in the page url.
	 * @return int|\WP_Error
	 */
	public static function publish_page( $title, $content, $is_template_no_title = false, $meta = false, $slug = null ) {
		$post = array(
			'post_title'   => $title,
			'post_status'  => 'publish',
			'post_content' => $content,
			'post_type'    => 'page',
		);

		if ( $meta ) {
			$post['meta_input'] = $meta;
		}

		if ( $is_template_no_title ) {
			$post['page_template'] = 'no-title';
		}

		if ( $slug && '' != $slug ) {
			$post['post_name'] = $slug;
		}

		return \wp_insert_post( $post );
	}

	/**
	 * Deletes a page by its name.
	 *
	 * @param string  $page_name The kebab-cased name for the page.
	 * @param boolean $trash Defines whether to trash or delete a page.
	 * @return boolean
	 */
	public static function delete_page_by_name( $page_name, $trash = true ) {
		$items = new \WP_Query(
			array(
				'pagename'    => $page_name,
				'post_status' => 'publish',
			)
		);

		$pages = $items->posts;

		if ( ! $pages || empty( $pages ) ) {
			return false;
		}

		foreach ( $pages as $page ) {
			wp_delete_post( $page->ID, ! $trash );
		}

		return true;
	}
}
