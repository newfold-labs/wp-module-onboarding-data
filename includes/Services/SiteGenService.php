<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

use NewfoldLabs\WP\Module\AI\SiteGen\SiteGen;
use NewfoldLabs\WP\Module\Onboarding\Data\Options;
use NewfoldLabs\WP\Module\Onboarding\Data\Data;
use NewfoldLabs\WP\Module\Onboarding\Data\Flows\Flows;
use NewfoldLabs\WP\Module\Onboarding\Data\Mustache\Mustache;
use NewfoldLabs\WP\Module\Onboarding\Data\Themes;
use NewfoldLabs\WP\Module\Onboarding\Data\Themes\Colors;
use NewfoldLabs\WP\Module\Onboarding\Data\Themes\Fonts;
use NewfoldLabs\WP\Module\Patterns\SiteClassification as PatternsSiteClassification;

/**
 * Class SiteGenService
 *
 * Class for handling SiteGen Interactions.
 */
class SiteGenService {

	/**
	 * Onboarding to SiteGen identifier map.
	 *
	 * @var array
	 */
	private static $identifiers = array(
		'site_classification'   => 'siteclassification',
		'target_audience'       => 'targetaudience',
		'content_tones'         => 'contenttones',
		'content_structure'     => 'contentstructure',
		'color_palette'         => 'colorpalette',
		'sitemap'               => 'sitemap',
		'plugin_recommendation' => 'pluginrecommendation',
		'font_pair'             => 'fontpair',
		'site_config'           => 'siteconfig',
	);

	/**
	 * Get SiteGen identifier from an Onboarding identifier key.
	 *
	 * @param string $identifier_key Onboarding identifier key.
	 * @return string|false
	 */
	public static function get_identifier_name( $identifier_key ) {
		return isset( self::$identifiers[ $identifier_key ] ) ? self::$identifiers[ $identifier_key ] : false;
	}

	/**
	 * Gets Valid Identifiers.
	 *
	 * @return array
	 */
	public static function enabled_identifiers() {
		return array(
			'site_classification'   => true,
			'target_audience'       => true,
			'content_tones'         => true,
			'content_structure'     => true,
			'color_palette'         => true,
			'sitemap'               => true,
			'plugin_recommendation' => true,
			'font_pair'             => true,
			'site_config'           => true,
		);
	}

	/**
	 * Determines whether the given identifier is valid.
	 *
	 * @param string $key The identifier to be evaluated.
	 * @return boolean
	 */
	public static function is_identifier( $key ) {
		return isset( self::enabled_identifiers()[ $key ] );
	}

	/**
	 * Checks if the site is eligible for SiteGen Capabilities.
	 *
	 * @return boolean
	 */
	public static function is_enabled() {
		if ( ! ( class_exists( 'NewfoldLabs\WP\Module\AI\SiteGen\SiteGen' ) ) ) {
			return false;
		}
		return isset( Data::current_brand()['config']['enabled_flows']['sitegen'] )
		&& true === Data::current_brand()['config']['enabled_flows']['sitegen'];
	}


	/**
	 * Sends the data required for SiteGen Generation
	 *
	 * @param string|Object $site_info The prompt that configures the Site gen object.
	 * @param string        $identifier The identifier for Generating Site Meta.
	 * @param boolean       $skip_cache To override the cache and fetch the data.
	 * @return array
	 */
	public static function instantiate_site_meta( $site_info, $identifier, $skip_cache = false ) {
		if ( ! self::is_identifier( $identifier ) ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Not a valid identifier', 'wp-module-onboarding' ),
				array(
					'status' => '400',
				)
			);
		}

		$identifier = self::get_identifier_name( $identifier );
		$response   = SiteGen::generate_site_meta( $site_info, $identifier, $skip_cache );
		if ( isset( $response['error'] ) ) {
			// Handle the error case by returning a WP_Error.
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Error generating site meta: ', 'wp-module-onboarding' ) . $identifier,
				array( 'status' => 400 )
			);
		}

		return $response;
	}

	/**
	 * Handle completion of the sitegen flow.
	 *
	 * @param array $active_homepage The active homepage that was customized.
	 * @param array $homepage_data All the other generated homepage options.
	 * @return boolean
	 */
	public static function complete( $active_homepage, $homepage_data ) {
		$show_pages_on_front = \get_option( Options::get_option_name( 'show_on_front', false ) );

		// Check if default homepage is posts.
		if ( 'posts' === $show_pages_on_front ) {
			\update_option( Options::get_option_name( 'show_on_front', false ), 'page' );
		}

		$title   = $active_homepage['title'];
		$content = $active_homepage['content'];
		$post_id = SitePagesService::publish_page(
			$title,
			$content,
			true,
			array(
				'nf_dc_page' => 'home',
			)
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		\update_option( Options::get_option_name( 'page_on_front', false ), $post_id );

		self::generate_child_theme( $active_homepage );

		foreach ( $homepage_data as $index => $data ) {
			if ( $data['isFavorite'] && $data['slug'] !== $active_homepage['slug'] ) {
				self::generate_child_theme( $data );
			}
		}

		ThemeGeneratorService::activate_theme( $active_homepage['slug'] );

		return true;
	}

	/**
	 * Generates a child theme for the sitegen flow.
	 *
	 * @param array $data Data on each homepage and it's corresponding styles.
	 * @return true|\WP_Error
	 */
	public static function generate_child_theme( $data ) {
		global $wp_filesystem;
		ThemeGeneratorService::connect_to_filesystem();
		$parent_theme_slug   = 'yith-wonder';
		$parent_theme_exists = ( \wp_get_theme( $parent_theme_slug ) )->exists();
		if ( ! $parent_theme_exists ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				'Parent theme is missing to generate the child theme.',
				array( 'status' => 500 )
			);
		}

		/*
		Activate the parent theme if it is not active.
		This is necessary to register the parent theme's block patterns.
		 */
		$active_theme = Themes::get_active_theme();
		if ( $active_theme !== $parent_theme_slug ) {
			ThemeGeneratorService::activate_theme( $parent_theme_slug );
		}

		// Generate the necessary slugs and paths.
		$themes_dir       = dirname( \get_stylesheet_directory() );
		$parent_theme_dir = $themes_dir . '/' . $parent_theme_slug;
		$child_theme_slug = $data['slug'];
		$child_theme_dir  = $themes_dir . '/' . $child_theme_slug;

		$theme_json_file = $parent_theme_dir . '/theme.json';
		if ( ! $wp_filesystem->exists( $theme_json_file ) ) {
			return false;
		}
		$theme_json      = $wp_filesystem->get_contents( $theme_json_file );
		$theme_json_data = json_decode( $theme_json, true );

		$theme_json_data['settings']['color']['palette'] = $data['color']['palette'];

		if ( ! $theme_json_data ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				'Could not generate theme.json',
				array( 'status' => 500 )
			);
		}

		$current_brand = Data::current_brand();
		$customer      = \wp_get_current_user();

		$default_site_titles_dashed = array( 'welcome', 'wordpress-site' );
		$site_title                 = \get_bloginfo( 'name' );
		$site_title_dashed          = \sanitize_title_with_dashes( $site_title );
		if ( empty( $site_title ) || in_array( $site_title_dashed, $default_site_titles_dashed, true ) ) {
			$site_title = $current_brand['brand'] . '-' . ThemeGeneratorService::get_site_url_hash();
		}

		$part_patterns = array();
		if ( ! empty( $data['header'] ) ) {
			$part_patterns['header'] = $data['header'];
		}

		if ( ! empty( $data['footer'] ) ) {
			$part_patterns['footer'] = $data['footer'];
		}

		$theme_style_data = array(
			'current_brand'     => Data::current_brand(),
			'brand'             => $current_brand['brand'],
			'brand_name'        => $current_brand['name'] ? $current_brand['name'] : 'Newfold Digital',
			'theme_name'        => $data['title'],
			'site_title'        => $site_title,
			'site_url'          => \site_url(),
			'author'            => $customer->user_firstname,
			'parent_theme_slug' => $parent_theme_slug,
			'child_theme_slug'  => $child_theme_slug,
		);

		$mustache                       = new Mustache();
		$child_theme_stylesheet_comment = $mustache->render_template( 'themeStylesheet', $theme_style_data );

		// Write the child theme to the filesystem under themes.
		$child_theme_data = array(
			'parent_theme_slug'              => $parent_theme_slug,
			'child_theme_slug'               => $child_theme_slug,
			'parent_theme_dir'               => $parent_theme_dir,
			'child_theme_dir'                => $child_theme_dir,
			'child_theme_json'               => \wp_json_encode( $theme_json_data ),
			'child_theme_stylesheet_comment' => $child_theme_stylesheet_comment,
			'part_patterns'                  => $part_patterns,
			'theme_screenshot_dir'           => realpath( __DIR__ . '/../assets/images' ),
			'theme_screenshot'               => isset( $data['screenshot'] ) ? $data['screenshot'] : false,
		);

		$child_theme_written = ThemeGeneratorService::write_child_theme( $child_theme_data );
		if ( true !== $child_theme_written ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				$child_theme_written,
				array( 'status' => 500 )
			);
		}

		return true;
	}

	/**
	 * Gets the preview homepages
	 *
	 * @param string $site_description Description of the site.
	 * @param array  $content_style Description of the content style.
	 * @param array  $target_audience Description of the target audience.
	 * @return array
	 */
	public static function generate_homepages( $site_description, $content_style, $target_audience ) {

		$homepages = SiteGen::get_home_pages(
			$site_description,
			$content_style,
			$target_audience,
			false
		);

		if ( isset( $homepages['error'] ) ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Error generating homepages: ', 'wp-module-onboarding' ),
				array( 'status' => 400 )
			);
		}

		$processed_homepages = self::process_homepages_response( $homepages );

		if ( is_wp_error( $processed_homepages ) ) {
			return $processed_homepages;
		}

		self::update_homepages( $processed_homepages );

		return $processed_homepages;
	}

	/**
	 * Regenerate previews for favourited homepages
	 *
	 * @param string $slug slug of the home page to be regenerated.
	 * @param array  $color_palette  color palatte.
	 * @return array
	 */
	public static function regenerate_favorite_homepage( $slug, $color_palette ) {
		$existing_homepages = self::get_homepages();
		if ( ! isset( $existing_homepages[ $slug ] ) ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'The homepage could not be found for regeneration.', 'wp-module-onboarding' ),
				array(
					'status' => 404,
				)
			);
		}
		$homepage = $existing_homepages[ $slug ];

		// Fetch the color palette data from the options table.
		$existing_color_palettes = self::get_color_palettes();

		if ( is_wp_error( $existing_color_palettes ) ) {
			return $existing_color_palettes;
		}

		// Decode the color palettes if it's not an array (assuming it's a JSON string).
		if ( ( is_string( $existing_color_palettes ) ) ) {
			$existing_color_palettes = json_decode( $existing_color_palettes, true );
		}

		// Select a random palette and check against the parent's palette.
		$palette_index    = array_rand( $existing_color_palettes );
		$selected_palette = self::transform_palette( $existing_color_palettes[ $palette_index ], $palette_index );

		// If regeneration is true and the selected palette matches the parent's palette, reselect.
		$palette_count = count( $existing_color_palettes );
		while ( $selected_palette === $color_palette && $palette_count > 1 ) {
			$palette_index    = array_rand( $existing_color_palettes );
			$selected_palette = self::transform_palette( $existing_color_palettes[ $palette_index ], $palette_index );
		}

		$homepage['slug']  .= '-copy';
		$homepage['title'] .= __( ' (Copy)', 'wp-module-onboarding' );
		$homepage['color']  = $selected_palette;

		while ( isset( $existing_homepages[ $homepage['slug'] ] ) ) {
			$homepage['slug']  .= '-copy';
			$homepage['title'] .= __( ' (Copy)', 'wp-module-onboarding' );
		}

		$existing_homepages[ $homepage['slug'] ] = $homepage;
		self::update_homepages( $existing_homepages );

		return $homepage;
	}

	/**
	 * Regenerate previews homepages
	 *
	 * @param string $site_description Description of the site.
	 * @param array  $content_style Description of the content style.
	 * @param array  $target_audience Description of the target audience.
	 * @return array
	 */
	public static function regenerate_homepage( $site_description, $content_style, $target_audience ) {
		$existing_homepages    = self::get_homepages();
		$regenerated_homepages = self::get_regenerated_homepages();

		if ( ! empty( $regenerated_homepages ) ) {
			$regenerated_homepage                                = array_shift( $regenerated_homepages );
			$existing_homepages[ $regenerated_homepage['slug'] ] = $regenerated_homepage;
			self::update_homepages( $existing_homepages );
			self::update_regenerated_homepages( $regenerated_homepages );
			return $regenerated_homepage;
		}

		$regenerated_homepages = SiteGen::get_home_pages( $site_description, $content_style, $target_audience, true );
		if ( isset( $homepages['error'] ) ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Error re-generating homepages: ', 'wp-module-onboarding' ),
				array( 'status' => 400 )
			);
		}
		$processed_regenerated_homepages                     = self::process_homepages_response( $regenerated_homepages );
		$regenerated_homepage                                = array_shift( $processed_regenerated_homepages );
		$existing_homepages[ $regenerated_homepage['slug'] ] = $regenerated_homepage;
		self::update_homepages( $existing_homepages );
		self::update_regenerated_homepages( $processed_regenerated_homepages );
		return $regenerated_homepage;
	}

	/**
	 * Processes the Homepages response structure for homepages
	 *
	 * @param array $homepages array.
	 * @return array
	 */
	public static function process_homepages_response(
		$homepages
	) {
		$processed_homepages = array();
		// Fetch the color palette data from the options table.
		$color_palettes = self::get_color_palettes();

		if ( is_wp_error( $color_palettes ) ) {
			return $color_palettes;
		}
		// Retrieve the existing homepages to find the last version number.
		$existing_homepages  = self::get_homepages();
		$last_version_number = self::get_last_version_number( $existing_homepages );
		$version_number      = $last_version_number + 1;

		foreach ( $homepages as $slug => $data ) {

			// Select a random palette and check against the parent's palette.
			$palette_index    = array_rand( $color_palettes );
			$selected_palette = self::transform_palette( $color_palettes[ $palette_index ], $palette_index );

			$homepage_slug                         = 'version-' . $version_number;
			$processed_homepages[ $homepage_slug ] = array(
				'slug'       => $homepage_slug,
				'title'      => __( 'Version ', 'wp-module-onboarding' ) . $version_number,
				'isFavorite' => false,
				'content'    => $data['content'],
				'header'     => $data['header'],
				'footer'     => $data['footer'],
				'color'      => $selected_palette,
			);
			++$version_number;
		}

		return $processed_homepages;
	}


	/**
	 * Get color palattes from the SiteGen meta.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_color_palettes() {
		$prompt = self::get_prompt();
		if ( ! $prompt ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Prompt not found.', 'wp-module-onboarding' ),
				array( 'status' => 404 )
			);
		}

		$color_palette = self::instantiate_site_meta(
			array(
				'site_description' => $prompt,
			),
			'color_palette'
		);

		if ( is_wp_error( $color_palette ) ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Cannot retrieve color palatte', 'wp-module-onboarding' ),
				array( 'status' => 400 )
			);
		}

		$color_palettes = array();
		if ( is_int( array_rand( $color_palette ) ) ) {
			$color_palettes = $color_palette;
		} else {
			$color_palettes[] = $color_palette;
		}
		return $color_palettes;
	}

	/**
	 * Get the last version number to increment excluiding the (copy) versions
	 *
	 * @param array $homepages unstructured home pages from sitegen ai responses.
	 * @return array
	 */
	public static function get_last_version_number( $homepages ) {
		// Initialize to zero, assuming there are no versions yet.
		$last_version_number = 0;

		// Loop through the homepages to find the highest version number.
		foreach ( $homepages as $slug => $data ) {
			// Extract the number from the slug (assuming slug is like "version3").
			if ( preg_match( '/version-(\d+)$/', $slug ) ) {
				++$last_version_number;
			}
		}

		return $last_version_number;
	}

	/**
	 * Transform the color palatte structure for response
	 *
	 * @param array  $palette color platte chosen to transform.
	 * @param string $palette_index color palatte index.
	 * @return array
	 */
	public static function transform_palette( $palette, $palette_index ) {
		$palette_name        = 'palette-' . ( $palette_index + 1 );
		$transformed_palette = array(
			'slug'    => $palette_name,
			'palette' => array_map(
				function ( $key, $value ) {
					return array(
						'slug'  => $key,
						'name'  => ucfirst( str_replace( '_', ' ', $key ) ),
						'color' => $value,
					);
				},
				array_keys( $palette ),
				$palette
			),
		);

		return $transformed_palette;
	}

	/**
	 * Get Plugin recommendations from the SiteGen meta.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_plugin_recommendations() {
		$flow_data = \get_option( Options::get_option_name( 'flow' ), false );
		if ( ! $flow_data || empty( $flow_data['sitegen']['siteDetails']['prompt'] ) ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Prompt not found.', 'wp-module-onboarding' ),
				array( 'status' => 404 )
			);
		}

		$prompt                 = $flow_data['sitegen']['siteDetails']['prompt'];
		$plugin_recommendations = self::instantiate_site_meta(
			array(
				'site_description' => $prompt,
			),
			'plugin_recommendation'
		);

		if ( isset( $plugin_recommendations['error'] ) || is_wp_error( $plugin_recommendations ) ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Cannot retrieve plugin recommendations.', 'wp-module-onboarding' ),
				array( 'status' => 400 )
			);
		}

		$priority                  = 0;
		$recommended_plugins       = isset( $plugin_recommendations['recommendedPlugins'] ) ? $plugin_recommendations['recommendedPlugins'] : array();
		$final_recommended_plugins = array();
		foreach ( $recommended_plugins as $recommended_plugin ) {
			$recommended_plugin['priority'] = $priority;
			$priority                      += 20;
			$recommended_plugin['activate'] = false;
			array_push( $final_recommended_plugins, $recommended_plugin );
		}

		$required_plugins       = isset( $plugin_recommendations['requiredPlugins'] ) ? $plugin_recommendations['requiredPlugins'] : array();
		$final_required_plugins = array();
		foreach ( $required_plugins as $required_plugin ) {
			$required_plugin['priority'] = $priority;
			$priority                   += 20;
			$required_plugin['activate'] = true;
			array_push( $final_required_plugins, $required_plugin );
		}

		return array_merge( $final_required_plugins, $final_recommended_plugins );
	}

	/**
	 * Get SiteGen customize sidebar data.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_customize_sidebar_data() {
		$flow_data = get_option( Options::get_option_name( 'flow' ), false );
		if ( ! $flow_data || empty( $flow_data['sitegen']['siteDetails']['prompt'] ) ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				__( 'Prompt not found.', 'wp-module-onboarding' ),
				array( 'status' => 404 )
			);
		}

		$prompt        = $flow_data['sitegen']['siteDetails']['prompt'];
		$color_palette = self::instantiate_site_meta(
			array(
				'site_description' => $prompt,
			),
			'color_palette'
		);
		$font_pair     = self::instantiate_site_meta(
			array(
				'site_description' => $prompt,
			),
			'font_pair'
		);

		if ( is_wp_error( $color_palette ) ) {
			$color_palette = Colors::get_sitegen_color_palette_data();
		}

		if ( is_wp_error( $font_pair ) ) {
			$font_pair = Fonts::get_sitegen_font_data();
		}

		$default_design = Fonts::get_sitegen_default_design_data();

		return array(
			'design'        => $default_design,
			'colorPalettes' => $color_palette,
			'designStyles'  => $font_pair,
		);
	}

	/**
	 * Filters Wonder Blocks transients before they are set.
	 *
	 * @return void
	 */
	public static function pre_set_filter_wonder_blocks_transients() {
		$args = wp_parse_args(
			array(
				'primary_type'   => PatternsSiteClassification::get_primary_type(),
				'secondary_type' => PatternsSiteClassification::get_secondary_type(),
			)
		);
		$id   = md5( serialize( $args ) );

		\add_action( "pre_set_transient_wba_templates_{$id}", array( __CLASS__, 'filter_wonder_blocks_templates_transient' ), 10, 1 );
		\add_action( 'pre_set_transient_wba_templates_categories', array( __CLASS__, 'filter_wonder_blocks_categories_transient' ), 10, 1 );
	}

	/**
	 * Filters the Wonder Blocks templates transient.
	 *
	 * @param array $value The original value of the transient.
	 * @return array
	 */
	public static function filter_wonder_blocks_templates_transient( $value ) {
		if ( empty( $value ) || ! is_array( $value ) ) {
			return $value;
		}

		$homepages = self::get_homepages();
		if ( ! $homepages ) {
			return $value;
		}

		foreach ( $homepages as $index => $data ) {
			array_push(
				$value,
				array(
					'id'          => $data['slug'],
					'slug'        => $data['slug'],
					'description' => $data['slug'],
					'content'     => $data['content'],
					'categories'  => array( 'home', 'featured' ),
				)
			);
		}

		return $value;
	}

	/**
	 * Filters the Wonder Blocks categories transient.
	 *
	 * @param array $value The original value of the transient.
	 * @return array
	 */
	public static function filter_wonder_blocks_categories_transient( $value ) {
		if ( empty( $value ) || ! is_array( $value ) ) {
			return $value;
		}

		$homepages = self::get_homepages();
		if ( ! $homepages ) {
			return $value;
		}

		foreach ( $value as $index => $category ) {
			if ( 'home' === $category['title'] ) {
				$category['count'] = $category['count'] + count( $homepages );
				$value[ $index ]   = $category;
			}
		}

		return $value;
	}

	/**
	 * Fetches the homepages generated in the Sitegen flow.
	 *
	 * @return false|array
	 */
	public static function get_homepages() {
		$data = FlowService::read_data_from_wp_option( false );
		return isset( $data['sitegen']['homepages']['data'] ) ? $data['sitegen']['homepages']['data'] : false;
	}

	/**
	 * Get the prompt entered during the sitegen flow.
	 *
	 * @return string|false
	 */
	public static function get_prompt() {
		$data = FlowService::read_data_from_wp_option( false );
		return ! empty( $data['sitegen']['siteDetails']['prompt'] ) ? $data['sitegen']['siteDetails']['prompt'] : false;
	}

	/**
	 * Update the list of sitegen generated homepages.
	 *
	 * @param array $homepages The new list of homepages.
	 * @return boolean
	 */
	public static function update_homepages( $homepages ) {
		$data = FlowService::read_data_from_wp_option( false );
		if ( ! isset( $data['sitegen']['homepages']['data'] ) ) {
			return false;
		}

		$data['sitegen']['homepages']['data'] = $homepages;
		FlowService::update_data_in_wp_option( $data );
		return true;
	}

	/**
	 * Get the list of regenerated homepages.
	 *
	 * @return array
	 */
	public static function get_regenerated_homepages() {
		return \get_option( Options::get_option_name( 'sitegen_regenerated_homepages' ), array() );
	}

	/**
	 * Update the list of sitegen regenerated homepages.
	 *
	 * @param array $regenerated_homepages The new list of regenerated homepages.
	 * @return boolean
	 */
	public static function update_regenerated_homepages( $regenerated_homepages ) {
		\update_option( Options::get_option_name( 'sitegen_regenerated_homepages' ), $regenerated_homepages );
		return true;
	}

	/**
	 * Sets the sitemapPagesGenerated data in the flow.
	 *
	 * @param boolean $status The status of the generated sitemap pages.
	 * @return true
	 */
	public static function set_sitemap_pages_generated( $status ) {
		$data                                     = FlowService::read_data_from_wp_option( false );
		$data['sitegen']['sitemapPagesGenerated'] = $status;
		FlowService::update_data_in_wp_option( $data );
		return true;
	}

	/**
	 * Generate and publish the sitemap pages.
	 *
	 * @param string $site_description The description of the site (prompt).
	 * @param array  $content_style The type of content style.
	 * @param array  $target_audience The target audience meta.
	 * @param array  $sitemap The list of site pages and their keywords.
	 * @return true
	 */
	public static function publish_sitemap_pages( $site_description, $content_style, $target_audience, $sitemap ) {
		$other_pages = SiteGen::get_pages(
			$site_description,
			$content_style,
			$target_audience,
			$sitemap,
			false
		);

		// TODO: Improve error handling to reliably determine if a page has been published or not instead of trying and returning true.
		foreach ( $sitemap as $index => $page ) {
			if ( ! isset( $other_pages[ $page['slug'] ] ) || isset( $other_pages[ $page['slug'] ]['error'] ) ) {
				continue;
			}

			$page_content = $other_pages[ $page['slug'] ];
			SitePagesService::publish_page(
				$page['title'],
				$page_content,
				true,
				array(
					'nf_dc_page' => $page['slug'],
				)
			);
		}

		self::set_sitemap_pages_generated( true );

		return true;
	}

	/**
	 * Adding action hooks that trigger the AI Module generates site meta.
	 * This needs to be added before the do_action is triggered from AI Module
	 *
	 * @return void
	 */
	public static function instantiate_sitegen_hooks() {
		\add_action( 'newfold/ai/sitemeta-siteconfig:generated', array( __CLASS__, 'set_site_title_and_tagline' ), 10, 1 );
	}

	/**
	 * Sets the Title and Tagline for the site.
	 *
	 * @param array $site_details The Site title and site tagline.
	 * @return boolean
	 */
	public static function set_site_title_and_tagline( $site_details ) {

		// Updates the Site Title
		if ( ( ! empty( $site_details['site_title'] ) ) ) {
			\update_option( Options::get_option_name( 'blog_name', false ), $site_details['site_title'] );
		}

		// Updates the Site Desc (Tagline)
		if ( ( ! empty( $site_details['tagline'] ) ) ) {
			\update_option( Options::get_option_name( 'blog_description', false ), $site_details['tagline'] );
		}
		return true;
	}
}
