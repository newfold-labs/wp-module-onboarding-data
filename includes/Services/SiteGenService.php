<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

use NewfoldLabs\WP\Module\AI\SiteGen\SiteGen;
use NewfoldLabs\WP\Module\Onboarding\Data\Options;
use NewfoldLabs\WP\Module\Onboarding\Data\Data;
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
		return SiteGen::generate_site_meta( $site_info, $identifier, $skip_cache );
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

		// Check if default homepage is posts
		if ( 'posts' === $show_pages_on_front ) {
			\update_option( Options::get_option_name( 'show_on_front', false ), 'page' );
		}

		foreach ( $homepage_data as $slug => $data ) {
			if ( ! $data['favorite'] && $slug !== $active_homepage['slug'] ) {
				continue;
			}
			$title   = $data['title'];
			$content = $data['content'];
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
			if ( $active_homepage['slug'] === $slug ) {
				\update_option( Options::get_option_name( 'page_on_front', false ), $post_id );
			}

			self::generate_child_theme( $data );

		}

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
		);

		$child_theme_written = ThemeGeneratorService::write_child_theme( $child_theme_data );
		if ( true !== $child_theme_written ) {
			return new \WP_Error(
				'nfd_onboarding_error',
				$child_theme_written,
				array( 'status' => 500 )
			);
		}

		// Activate the child theme.
		if ( true === $data['favorite'] ) {
			ThemeGeneratorService::activate_theme( $child_theme_slug );
		}

		return true;
	}

	/**
	 * Get Plugin recommendations from the SiteGen meta.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_plugin_recommendations() {
		$flow_data = get_option( Options::get_option_name( 'flow' ), false );
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
			$recommended_plugin['slug']     = explode( '/', $recommended_plugin['slug'] )[0];
			$recommended_plugin['priority'] = $priority;
			$priority                      += 20;
			$recommended_plugin['activate'] = false;
			array_push( $final_recommended_plugins, $recommended_plugin );
		}

		$required_plugins       = isset( $plugin_recommendations['requiredPlugins'] ) ? $plugin_recommendations['requiredPlugins'] : array();
		$final_required_plugins = array();
		foreach ( $required_plugins as $required_plugin ) {
			$required_plugin['slug']     = explode( '/', $required_plugin['slug'] )[0];
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

		if ( isset( $color_palette['error'] ) || is_wp_error( $color_palette ) ) {
			$color_palette = Colors::get_sitegen_color_palette_data();
		}

		if ( isset( $font_pair['error'] ) || is_wp_error( $font_pair ) ) {
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

		$homepages = FlowService::get_sitegen_homepages();
		if ( ! $homepages ) {
			return $value;
		}

		foreach ( $homepages as $slug => $data ) {
			array_push(
				$value,
				array(
					'id'          => $slug,
					'slug'        => $slug,
					'description' => $slug,
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

		$homepages = FlowService::get_sitegen_homepages();
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
}
