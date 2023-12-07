<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

class SitePagesService {
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

