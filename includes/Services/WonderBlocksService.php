<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

use NewfoldLabs\WP\Module\Onboarding\Data\Data;
use NewfoldLabs\WP\Module\Data\WonderBlocks\WonderBlocks;
use NewfoldLabs\WP\Module\Data\SiteClassification\PrimaryType;
use NewfoldLabs\WP\Module\Data\SiteClassification\SecondaryType;
use NewfoldLabs\WP\Module\Data\WonderBlocks\Requests\Fetch as WonderBlocksFetchRequest;
use NewfoldLabs\WP\Module\Onboarding\Data\Patterns;

/**
 * Class WonderBlocksService
 *
 * Class for handling WonderBlock interactions.
 */
class WonderBlocksService {

	/**
	 * Determines whether a slug is a pattern.
	 *
	 * @param string $slug The slug to evaluate.
	 * @return boolean
	 */
	public static function is_pattern( $slug ) {
		$patterns = array(
			'header-1'  => true,
			'header-10' => true,
			'header-8'  => true,
			'header-3'  => true,
			'footer-15' => true,
		);

		return isset( $patterns[ $slug ] );
	}

		/**
		 * Determines whether a slug is a template.
		 *
		 * @param string $slug The slug to evaluate.
		 * @return boolean
		 */
	public static function is_template( $slug ) {
		$templates = array(
			'home-1'                  => true,
			'home-2'                  => true,
			'home-3'                  => true,
			'about-4'                 => true,
			'contact-4'               => true,
			'testimonials-template-2' => true,
		);

		return isset( $templates[ $slug ] );
	}

	/**
	 * Get the slug for a given pattern name.
	 *
	 * Valid slugs have `wonder-blocks/` prefixed to the actual name.
	 *
	 * @param string $name The name of the pattern/template.
	 * @return string
	 */
	public static function add_prefix_to_name( $name ) {
		return "wonder-blocks/{$name}";
	}

	/**
	 * Given a full slug of WonderBlocks, strip the wonder-blocks prefix.
	 *
	 * @param string $slug A valid WonderBlock slug.
	 * @return string
	 */
	public static function strip_prefix_from_slug( $slug ) {
		return explode( '/', $slug )[1];
	}

	/**
	 * Checks whether a given slug is a valid WonderBlock slug.
	 *
	 * @param string $slug The slug of the pattern/template.
	 * @return boolean
	 */
	public static function is_valid_slug( $slug ) {
		$wonder_blocks_slug = explode( '/', $slug );
		if ( isset( $wonder_blocks_slug[0] ) && 'wonder-blocks' === $wonder_blocks_slug[0] && isset( $wonder_blocks_slug[1] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if the site is eligible for WonderBlocks.
	 *
	 * @return boolean
	 */
	public static function is_enabled() {
		if ( ! ( class_exists( 'NewfoldLabs\WP\Module\Data\WonderBlocks\Requests\Fetch' )
		&& class_exists( 'NewfoldLabs\WP\Module\Data\WonderBlocks\WonderBlocks' ) )
		&& class_exists( 'NewfoldLabs\WP\Module\Data\SiteClassification\PrimaryType' )
		&& class_exists( 'NewfoldLabs\WP\Module\Data\SiteClassification\SecondaryType' ) ) {
			return false;
		}
		return isset( Data::current_brand()['config']['wonder_blocks'] )
		&& true === Data::current_brand()['config']['wonder_blocks'];
	}

	/**
	 * Get wonder blocks data from a given template/pattern slug.
	 *
	 * @param string $slug The wonder blocks slug.
	 * @return array|false
	 */
	public static function get_data_from_slug( $slug ) {
		$wonder_blocks_slug = self::strip_prefix_from_slug( $slug );
		if ( self::is_pattern( $wonder_blocks_slug ) ) {
			return self::get_pattern_from_slug( $slug );
		}

		return self::get_template_from_slug( $slug );
	}

	/**
	 * Fetches the template from WonderBlocks given the template slug.
	 *
	 * @param string $template_slug The template slug.
	 * @return array|false
	 */
	public static function get_template_from_slug( $template_slug ) {
		$primary_type = PrimaryType::instantiate_from_option();
		if ( ! $primary_type ) {
			return false;
		}
		$secondary_type = SecondaryType::instantiate_from_option();
		if ( ! $secondary_type ) {
			return false;
		}

		$wonder_blocks_slug = self::strip_prefix_from_slug( $template_slug );
		$request            = new WonderBlocksFetchRequest(
			array(
				'endpoint'       => 'templates',
				'slug'           => $wonder_blocks_slug,
				'primary_type'   => $primary_type->value,
				'secondary_type' => $secondary_type->value,
			)
		);
		$template           = WonderBlocks::fetch( $request );

		if ( ! empty( $template ) ) {
			$template['categories'] = array( $template['categories'], 'yith-wonder-pages' );
			$template['name']       = $template['slug'];
			return array(
				'slug'       => $template_slug,
				'title'      => $template['title'],
				'content'    => $template['content'],
				'name'       => $template['name'],
				'meta'       => Patterns::get_meta_from_slug( $template_slug ),
				'categories' => $template['categories'],
			);
		}

		return false;
	}

	/**
	 * Fetches the pattern from WonderBlocks given the pattern slug.
	 *
	 * @param string $pattern_slug The pattern slug.
	 * @return array|false
	 */
	public static function get_pattern_from_slug( $pattern_slug ) {
		$primary_type = PrimaryType::instantiate_from_option();
		if ( ! $primary_type ) {
			return false;
		}
		$secondary_type = SecondaryType::instantiate_from_option();
		if ( ! $secondary_type ) {
			return false;
		}

		$wonder_blocks_slug = self::strip_prefix_from_slug( $pattern_slug );
		$request            = new WonderBlocksFetchRequest(
			array(
				'endpoint'       => 'patterns',
				'slug'           => $wonder_blocks_slug,
				'primary_type'   => $primary_type->value,
				'secondary_type' => $secondary_type->value,
			)
		);
		$patterns           = WonderBlocks::fetch( $request );

		if ( ! empty( $patterns ) ) {
			$patterns['categories'] = array( $patterns['categories'], 'yith-wonder-pages' );
			$patterns['name']       = $patterns['slug'];
			return array(
				'slug'       => $pattern_slug,
				'title'      => $patterns['title'],
				'content'    => $patterns['content'],
				'name'       => $patterns['name'],
				'meta'       => Patterns::get_meta_from_slug( $pattern_slug ),
				'categories' => $patterns['categories'],
			);
		}

		return false;
	}

	/**
	 * Clear the cache for a template slug fetched via get_template_from_slug.
	 *
	 * @param string $template_slug Slug of the template previously fetched via get_template_from_slug.
	 * @return boolean
	 */
	public static function delete_templates_cache_from_slug( $template_slug ) {
		$wonder_blocks_slug = self::strip_prefix_from_slug( $template_slug );

		$primary_type = PrimaryType::instantiate_from_option();
		if ( ! $primary_type ) {
			return false;
		}
		$secondary_type = SecondaryType::instantiate_from_option();
		if ( ! $secondary_type ) {
			return false;
		}

		$request = new WonderBlocksFetchRequest(
			array(
				'endpoint'       => 'templates',
				'slug'           => $wonder_blocks_slug,
				'primary_type'   => $primary_type->value,
				'secondary_type' => $secondary_type->value,
			)
		);

		return WonderBlocks::clear_cache( $request );
	}

	/**
	 * Get static homepages for the onboarding flow.
	 * Each homepage consists of a header, template, and footer.
	 * Uses WordPress transients to ensure consistency across all requests.
	 *
	 * @return array
	 */
	public static function get_fallback_homepages() {

		$cached_homepages = get_transient( 'nfd_fallback_homepages_cache' );
		
		if ( $cached_homepages !== false ) {
			return $cached_homepages;
		}

		$fallback_homepages = array();

		$homepage_configs = array(
			'homepage-2' => array(
				'header'   => 'wonder-blocks/header-14',
				'template' => 'wonder-blocks/home-2',
				'footer'   => 'wonder-blocks/footer-12',
				'title'    => 'Bold & Dynamic',
				'slug'     => 'homepage-2',
				'description' => 'A bold and dynamic design that makes a strong impression.',
			),
			'homepage-3' => array(
				'header'   => 'wonder-blocks/header-15',
				'template' => 'wonder-blocks/home-3',
				'footer'   => 'wonder-blocks/footer-14',
				'title'    => 'Contemporary Style',
				'slug'     => 'homepage-3',
				'description' => 'A contemporary design with modern aesthetics and clean lines.',
			),
			'homepage-4' => array(
				'header'   => 'wonder-blocks/header-16',
				'template' => 'wonder-blocks/home-4',
				'footer'   => 'wonder-blocks/footer-6',
				'title'    => 'Minimalist Elegance',
				'slug'     => 'homepage-4',
				'description' => 'A minimalist design that focuses on simplicity and elegance.',
			),
			'homepage-5' => array(
				'header'   => 'wonder-blocks/header-17',
				'template' => 'wonder-blocks/home-5',
				'footer'   => 'wonder-blocks/footer-11',
				'title'    => 'Warm & Welcoming',
				'slug'     => 'homepage-5',
				'description' => 'A warm and welcoming design that creates a friendly atmosphere.',
			),
		);

		// Shuffle the configurations to get random order
		$config_keys = array_keys( $homepage_configs );
		shuffle( $config_keys );

		// Take the first 3 configurations
		$selected_configs = array_slice( $config_keys, 0, 3 );

		foreach ( $selected_configs as $key ) {
			$homepage = self::create_fallback_homepage( $homepage_configs[ $key ] );
			if ( $homepage ) {
				$fallback_homepages[ $key ] = $homepage;
			}
		}

		// Cache the result in WordPress transients for 1 hour (3600 seconds)
		// This ensures consistency across all requests during the onboarding session
		set_transient( 'nfd_fallback_homepages_cache', $fallback_homepages, 3600 );

		return $fallback_homepages;
	}

	/**
	 * Create a static homepage by combining header, template, and footer.
	 *
	 * @param array $config Configuration containing header, template, footer slugs and metadata.
	 * @return array|false
	 */
	private static function create_fallback_homepage( $config ) {
		$header_data   = self::get_pattern_from_slug( $config['header'] );
		$template_data = self::get_template_from_slug( $config['template'] );
		$footer_data   = self::get_pattern_from_slug( $config['footer'] );

		if ( ! $header_data || ! $template_data || ! $footer_data ) {
			return false;
		}

		// Combine the content from header, template, and footer
		$combined_content = $header_data['content'] . "\n\n" . $template_data['content'] . "\n\n" . $footer_data['content'];

		return array(
			'slug'        => $config['slug'],
			'title'       => $config['title'],
			'description' => $config['description'],
			'header'      => $header_data['content'],
			'content'     => $template_data['content'],
			'footer'      => $footer_data['content'],
			'fullContent' => $combined_content,
			'color'       => array(
				'palette' => array(),
			),
			'screenshot'  => null,
			'iframeSrc'   => null,
			'postId'      => null,
			'components'  => array(
				'header'   => $header_data,
				'template' => $template_data,
				'footer'   => $footer_data,
			),
		);
	}

	/**
	 * Clear the static homepages cache.
	 * Useful for testing or when you need fresh randomization.
	 *
	 * @return void
	 */
	public static function clear_fallback_homepages_cache() {
		delete_transient( 'nfd_fallback_homepages_cache' );
	}
}
