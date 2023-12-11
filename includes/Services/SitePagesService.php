<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

/**
 * Contains functionality for managing a site's pages.
 */
class SitePagesService {
	/**
	 * Publish a new site page.
	 *
	 * @param string  $title The title of the page.
	 * @param string  $content The content(block grammar/text) that will be displayed on the page.
	 * @param boolean $is_template_no_title checks for title
	 * @param boolean $meta The page post_meta.
	 * @return int|\WP_Error
	 */
	public static function publish_page( $title, $content, $is_template_no_title = false, $meta = false ) {
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

		return \wp_insert_post( $post );
	}


}

