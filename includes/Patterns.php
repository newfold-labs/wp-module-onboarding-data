<?php
namespace NewfoldLabs\WP\Module\Onboarding\Data;

use NewfoldLabs\WP\Module\Onboarding\Data\Services\SiteGenService;
use NewfoldLabs\WP\Module\Onboarding\Data\Services\WonderBlocksService;

/**
 * Class Patterns
 */
final class Patterns {

	/**
	 * List of dummy menu page titles.
	 *
	 * @return array
	 */
	public static function get_dummy_navigation_menu_items() {
		$default_items = array(
			__( 'Home', 'wp-module-onboarding-data' ),
			__( 'About', 'wp-module-onboarding-data' ),
			__( 'Contact', 'wp-module-onboarding-data' ),
			__( 'News', 'wp-module-onboarding-data' ),
			__( 'Privacy', 'wp-module-onboarding-data' ),
			__( 'Careers', 'wp-module-onboarding-data' ),
		);

		$current_flow = Data::current_flow();
		if ( 'sitegen' === $current_flow ) {
			$sitegen_dummy_navigation_menu_items = SiteGenService::get_dummy_navigation_menu_items();
			if ( ! empty( $sitegen_dummy_navigation_menu_items ) ) {
				return $sitegen_dummy_navigation_menu_items;
			}
		}

		return $default_items;
	}

	/**
	 * Retrieve Patterns for Theme Step.
	 *
	 * @return array
	 */
	protected static function get_theme_step_patterns() {
		return array(
			'yith-wonder' => array(
				'theme-styles'    => array(
					'site-header-left-logo-navigation-inline' => array(
						'active'        => true,
						'replace'       => true,
						'wonder_blocks' => 'header-1',
					),
					'homepage-1'  => array(
						'active'        => true,
						'shown'         => true,
						'combine'       => true,
						'wonder_blocks' => 'home-1',
					),
					'site-footer' => array(
						'active'        => true,
						'wonder_blocks' => 'footer-15',
					),
				),
				'homepage-styles' => array(
					'site-header-left-logo-navigation-inline' => array(
						'active'        => true,
						'replace'       => true,
						'wonder_blocks' => 'header-1',
					),
					'homepage-1'  => array(
						'active'        => true,
						'shown'         => true,
						'wonder_blocks' => 'home-1',
					),
					'homepage-2'  => array(
						'active'        => true,
						'shown'         => true,
						'wonder_blocks' => 'home-2',
					),
					'homepage-3'  => array(
						'active'        => true,
						'shown'         => true,
						'wonder_blocks' => 'home-3',
					),
					'site-footer' => array(
						'active'        => true,
						'wonder_blocks' => 'footer-15',
					),
				),
				'site-pages'      => array(
					'company-page'      => array(
						'key'           => 'about',
						'active'        => true,
						'title'         => __( 'About', 'wp-module-onboarding-data' ),
						'selected'      => true,
						'shown'         => true,
						'description'   => __( 'Explain your company values or the history behind your brand.', 'wp-module-onboarding-data' ),
						'wonder_blocks' => 'about-4',
					),
					'contact-us'        => array(
						'key'           => 'contact',
						'active'        => true,
						'selected'      => true,
						'title'         => __( 'Contact', 'wp-module-onboarding-data' ),
						'shown'         => true,
						'description'   => __( 'Offer visitors a single page with a contact form, your street address and social media.', 'wp-module-onboarding-data' ),
						'wonder_blocks' => 'contact-4',
					),
					'testimonials-page' => array(
						'key'           => 'testimonials',
						'active'        => true,
						'title'         => __( 'Testimonials', 'wp-module-onboarding-data' ),
						'selected'      => false,
						'shown'         => true,
						'description'   => __( 'Highlight your success with testimonials from your fans.', 'wp-module-onboarding-data' ),
						'wonder_blocks' => 'testimonials-template-2',
					),
					'blog-page'         => array(
						'key'         => 'blog',
						'active'      => true,
						'selected'    => true,
						'title'       => __( 'Blog', 'wp-module-onboarding-data' ),
						'shown'       => true,
						'description' => __( 'A page for periodic news, announcements and ideas.', 'wp-module-onboarding-data' ),
					),
				),
				'header-menu'     => array(
					'site-header-left-logo-navigation-inline' => array(
						'active'        => true,
						'shown'         => true,
						'wonder_blocks' => 'header-1',
					),
					'homepage-1'                => array(
						'active'        => true,
						'wonder_blocks' => 'home-1',
					),
					'site-footer'               => array(
						'active'        => true,
						'wonder_blocks' => 'footer-15',
					),
					'site-header-left-logo-navigation-below' => array(
						'active'        => true,
						'shown'         => true,
						'wonder_blocks' => 'header-10',
					),
					'site-header-centered'      => array(
						'active'        => true,
						'shown'         => true,
						'wonder_blocks' => 'header-8',
					),
					'site-header-splitted-menu' => array(
						'active'        => true,
						'shown'         => true,
						'wonder_blocks' => 'header-3',
					),
				),
			),
		);
	}

	/**
	 * Define fallback patterns incase the primary ones cannot be found.
	 *
	 * @return array
	 */
	public static function get_fallbacks() {
		return array(
			'wonder-blocks' => array(
				'home-1'                  => 'yith-wonder/homepage-1',
				'home-2'                  => 'yith-wonder/homepage-2',
				'home-3'                  => 'yith-wonder/homepage-3',
				'about-4'                 => 'yith-wonder/company-page',
				'contact-4'               => 'yith-wonder/contact-us',
				'testimonials-template-2' => 'yith-wonder/testimonials-page',
				'header-1'                => 'yith-wonder/site-header-left-logo-navigation-inline',
				'header-3'                => 'yith-wonder/site-header-centered',
				'header-8'                => 'yith-wonder/site-header-left-logo-navigation-below',
				'header-10'               => 'yith-wonder/site-header-splitted-menu',
				'footer-15'               => 'yith-wonder/site-footer',
			),
		);
	}

	/**
	 * Callback Functions for Theme Step.
	 *
	 * @return array
	 */
	protected static function get_theme_step_filters() {
		return array(
			'yith-wonder' => array(
				'homepage-styles' => array( __CLASS__, 'filter_yith_wonder_homepage_patterns' ),
				'header-menu'     => array( __CLASS__, 'filter_yith_wonder_headermenu_patterns' ),
			),
		);
	}

	/**
	 * Get post metadata for a pattern. Ref: SitePagesController.php
	 *
	 * @return array
	 */
	public static function get_theme_patterns_meta() {
		return array(
			'yith-wonder'   => array(
				'company-page' => array(
					'nf_dc_page' => 'about',
				),
				'contact-us'   => array(
					'nf_dc_page' => 'contact',
				),
				'homepage-1'   => array(
					'nf_dc_page' => 'home',
				),
				'homepage-2'   => array(
					'nf_dc_page' => 'home',
				),
				'homepage-3'   => array(
					'nf_dc_page' => 'home',
				),
			),
			'wonder-blocks' => array(
				'home-1' => array(
					'nf_dc_page' => 'home',
				),
				'home-2' => array(
					'nf_dc_page' => 'home',
				),
				'home-3' => array(
					'nf_dc_page' => 'home',
				),
			),
		);
	}

	/**
	 * Sanitize the content by cleaning wp_grammar.
	 *
	 * @param string $content Data to clean.
	 *
	 * @return string
	 */
	private static function cleanup_wp_grammar( $content ) {

		// Remove template-part if that exists.
		$content = preg_replace( '/^<!-- wp:template-part .* \/-->$/m', '', $content );

		// Create an array with the values you want to replace.
		$searches = array( "\n", "\t" );

		// Replace the line breaks and tabs with a empty string.
		$content = str_replace( $searches, '', $content );

		return $content;
	}

	/**
	 * Get the post meta for a given slug.
	 *
	 * @param string $slug The slug (theme/kebab-cased-name).
	 * @return array|boolean
	 */
	public static function get_meta_from_slug( $slug ) {
		$theme_pattern = explode( '/', $slug );
		if ( ! isset( $theme_pattern[0] ) || ! isset( $theme_pattern[1] ) ) {
			return false;
		}
		$theme_patterns_meta = self::get_theme_patterns_meta();
		return isset( $theme_patterns_meta[ $theme_pattern [0] ][ $theme_pattern [1] ] )
		? $theme_patterns_meta[ $theme_pattern [0] ][ $theme_pattern [1] ]
		: false;
	}

	/**
	 * Get the fallback slug for a given slug.
	 *
	 * @param string $pattern_slug The given slug.
	 * @return string|false
	 */
	public static function get_fallback_from_slug( $pattern_slug ) {
		$theme_pattern = explode( '/', $pattern_slug );
		if ( ! isset( $theme_pattern[0] ) || ! isset( $theme_pattern[1] ) ) {
			return false;
		}
		$fallbacks = self::get_fallbacks();
		return isset( $fallbacks[ $theme_pattern [0] ][ $theme_pattern [1] ] )
		? $fallbacks[ $theme_pattern [0] ][ $theme_pattern [1] ]
		: false;
	}

	/**
	 * Fetches a pattern from the WP_Block_Patterns_Registry.
	 *
	 * @param string $pattern_slug The full slug of the pattern.
	 * @return array|boolean
	 */
	public static function get_pattern_from_block_patterns_registry( $pattern_slug ) {
		$block_patterns_registry = \WP_Block_Patterns_Registry::get_instance();
		if ( $block_patterns_registry->is_registered( $pattern_slug ) ) {
			$pattern = $block_patterns_registry->get_registered( $pattern_slug );
			return array(
				'slug'       => $pattern_slug,
				'title'      => $pattern['title'],
				'content'    => self::cleanup_wp_grammar( $pattern['content'] ),
				'name'       => $pattern['name'],
				'meta'       => self::get_meta_from_slug( $pattern_slug ),
				'categories' => $pattern['categories'],
			);
		}
		return false;
	}

	/**
	 * Retrieve pattern from slug.
	 *
	 * @param string $pattern_slug Pattern Slug Data.
	 *
	 * @return array|boolean
	 */
	public static function get_pattern_from_slug( $pattern_slug ) {
		if ( WonderBlocksService::is_valid_slug( $pattern_slug ) ) {
			$pattern = WonderBlocksService::get_data_from_slug( $pattern_slug );
			if ( ! $pattern ) {
				$fallback_pattern_slug = self::get_fallback_from_slug( $pattern_slug );
				if ( ! $fallback_pattern_slug ) {
					return false;
				}

				return self::get_pattern_from_block_patterns_registry( $fallback_pattern_slug );
			}

			return $pattern;
		}

		return self::get_pattern_from_block_patterns_registry( $pattern_slug );
	}

	/**
	 * Retrieve the header menu slug from flow data.
	 *
	 * @return string|boolean
	 */
	private static function get_selected_header_from_flow_data() {
		// fetch the selected header menu slug from DB.
		$flow_data = \get_option( Options::get_option_name( 'flow' ), false );
		if ( ! $flow_data ) {
			return false;
		}

		if ( empty( $flow_data['data']['partHeader'] ) ) {
			return false;
		}

		$part_header = $flow_data['data']['partHeader'];
		$slug        = explode( '/', $flow_data['data']['partHeader'] )[1];

		if ( WonderBlocksService::is_valid_slug( $part_header ) ) {
			return array(
				'slug' => $slug,
				'type' => 'wonder-blocks',
			);
		}

		return array(
			'slug' => $slug,
			'type' => 'yith-wonder',
		);
	}

	/**
	 * Replace the header menu slug in the patterns array
	 *
	 * @param array $pattern_content pattern grammar that is to be modified.
	 *
	 * @return array
	 */
	private static function replace_split_menu_items( $pattern_content ) {
		$dummy_menu_grammar      = '';
		$menu_navigation_grammar = '<!-- wp:navigation-link {"isTopLevelLink":true} /-->';

		foreach ( self::get_dummy_navigation_menu_items() as $item ) {
			$dummy_menu_grammar = '<!-- wp:navigation-link {
				"isTopLevelLink":true, 
				"label":"' . strtolower( $item ) . '", 
				"title":"' . $item . '", 
				"url":"' . get_site_url() . '/' . strtolower( $item ) . '"
			} /-->';
			$pattern_content    = preg_replace( $menu_navigation_grammar, $dummy_menu_grammar, $pattern_content, 1 );
		}
		return $pattern_content;
	}

	/**
	 * Retrieve Theme Step Patterns from chosen Theme in Previous Step
	 *
	 * @param string  $step Step from which Theme Step Pattern is required.
	 * @param boolean $squash Flag set to retrieve the block pattern.
	 *
	 * @return array|string
	 */
	public static function get_theme_step_patterns_from_step( $step, $squash = false ) {
		$active_theme = Themes::get_active_theme();

		if ( ! isset( self::get_theme_step_patterns()[ $active_theme ][ $step ] ) ) {
			return false;
		}

		$pattern_slugs  = self::get_theme_step_patterns()[ $active_theme ][ $step ];
		$block_patterns = $squash ? '' : array();

		foreach ( array_keys( $pattern_slugs ) as $pattern_slug ) {
			if ( true !== $pattern_slugs[ $pattern_slug ]['active'] ) {
				continue;
			}
			if ( isset( $pattern_slugs[ $pattern_slug ]['replace'] ) && true === $pattern_slugs[ $pattern_slug ]['replace'] ) {
				$pattern_slug_data = $pattern_slugs[ $pattern_slug ];
				$header_menu_data  = self::get_selected_header_from_flow_data();
				if ( ! empty( $header_menu_data ) ) {
					$slug = $header_menu_data['slug'];
					if ( 'wonder-blocks' === $header_menu_data['type'] ) {
						$pattern_slug_data['wonder_blocks'] = $slug;
					} else {
						// to update the chosen header from the fallback in the homelayouts page(wp-setup/step/design/homepage-menu).
						$pattern_slug_data['wonder_blocks'] = array_search( $active_theme . '/' . $slug, self::get_fallbacks()['wonder-blocks'], true );
					}
				}
				$pattern_slugs[ $pattern_slug ] = $pattern_slug_data;
			}

			if ( isset( $pattern_slugs[ $pattern_slug ]['wonder_blocks'] ) && WonderBlocksService::is_enabled() ) {
				$pattern_full_slug = WonderBlocksService::add_prefix_to_name( $pattern_slugs[ $pattern_slug ]['wonder_blocks'] );
			} else {
				$pattern_full_slug = $active_theme . '/' . $pattern_slug;
			}

			$pattern = self::get_pattern_from_slug( $pattern_full_slug );
			if ( ! $pattern ) {
				continue;
			}

			if ( false !== stripos( $pattern_slug, 'split' ) ) {
				$pattern['content'] = self::replace_split_menu_items( $pattern['content'] );
			}

			if ( ! $squash ) {
				$block_patterns[] = array_merge(
					$pattern,
					$pattern_slugs[ $pattern_slug ]
				);
				continue;
			}
			$block_patterns .= self::cleanup_wp_grammar( $pattern['content'] );
		}

		if ( isset( self::get_theme_step_filters()[ $active_theme ][ $step ] ) ) {
			$step_filter         = self::get_theme_step_filters()[ $active_theme ][ $step ];
			$theme_step_callback = isset( $step_filter ) ? $step_filter : false;
			if ( is_callable( $theme_step_callback ) ) {
				return $theme_step_callback( $block_patterns );
			}
		}

		return $block_patterns;
	}

	/**
	 * Retrieve Homepage Menu Step Patterns
	 *
	 * @param array $patterns Step Patterns Data.
	 * @return array
	 */
	private static function filter_yith_wonder_homepage_patterns( $patterns ) {
		$header_content       = '';
		$homepage_style_slugs = array();
		$footer_content       = '';

		foreach ( $patterns as $index_key => $slug ) {
			if ( ( WonderBlocksService::is_valid_slug( $slug['slug'] ) && in_array( 'header', $slug['categories'], true ) ) || in_array( 'yith-wonder-site-header', $slug['categories'], true ) ) {
				$header_content = $slug['content'];
				continue;
			}
			if ( ( WonderBlocksService::is_valid_slug( $slug['slug'] ) && in_array( 'footer', $slug['categories'], true ) ) || in_array( 'yith-wonder-site-footer', $slug['categories'], true ) ) {
				$footer_content = $slug['content'];
				continue;
			}
			if ( in_array( 'yith-wonder-pages', $slug['categories'], true ) ) {
				array_push( $homepage_style_slugs, $slug );
			}
		}

		foreach ( $homepage_style_slugs as $key => $homepage_style ) {
			$homepage_style_slugs[ $key ]['content'] = $header_content . $homepage_style['content'] . $footer_content;
		}

		return $homepage_style_slugs;
	}

	/**
	 * Retrieve Header Menu Step Patterns
	 *
	 * @param array $patterns Step Patterns Data.
	 * @return array
	 */
	private static function filter_yith_wonder_headermenu_patterns( $patterns ) {
		$body_content      = '';
		$header_menu_slugs = array();
		foreach ( $patterns as $pattern_details ) {
			if ( ( WonderBlocksService::is_valid_slug( $pattern_details['slug'] ) && in_array( 'header', $pattern_details['categories'], true ) ) || in_array( 'yith-wonder-site-header', $pattern_details['categories'], true ) ) {
				$header_menu_slugs['pageHeaders'][] = $pattern_details;
				continue;
			}

			if ( ( WonderBlocksService::is_valid_slug( $pattern_details['slug'] ) && in_array( 'footer', $pattern_details['categories'], true ) ) || in_array( 'yith-wonder-site-footer', $pattern_details['categories'], true ) ) {
				$header_menu_slugs['pageFooter'] = $pattern_details;
			}

			$body_content                 .= $pattern_details['content'];
			$header_menu_slugs['pageBody'] = $body_content;
		}

		return $header_menu_slugs;
	}

	/**
	 * Retrieve Pattern Count.
	 *
	 * @return array
	 */
	public static function get_count_of_patterns() {
		$active_theme          = Themes::get_active_theme();
		$theme_step_patterns   = self::get_theme_step_patterns();
		$active_theme_patterns = isset( $theme_step_patterns[ $active_theme ] ) ? $theme_step_patterns[ $active_theme ] : array();

		$theme_pattern_count = array();
		foreach ( $active_theme_patterns as $theme_step => $patterns ) {
			$theme_step_count = 0;
			$combine_styles   = 1;
			foreach ( $patterns as $pattern => $pattern_data ) {
				if ( isset( $pattern_data['shown'] ) && true === $pattern_data['shown'] ) {
					++$theme_step_count;
				}
				if ( isset( $pattern_data['combine'] ) && true === $pattern_data['combine'] ) {
					$combine_styles = count( \WP_Theme_JSON_Resolver::get_style_variations() ) + 1;
				}
			}

			$theme_pattern_count[ $theme_step ] = array(
				'previewCount' => $combine_styles * $theme_step_count,
			);
		}
		return $theme_pattern_count;
	}
}
