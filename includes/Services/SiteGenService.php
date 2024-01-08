<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

use NewfoldLabs\WP\Module\AI\SiteGen\SiteGen;
use NewfoldLabs\WP\Module\Onboarding\Data\Options;
use NewfoldLabs\WP\Module\Onboarding\Data\Data;
use NewfoldLabs\WP\Module\Onboarding\Data\Mustache\Mustache;
use NewfoldLabs\WP\Module\Onboarding\Data\Themes;

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
		$font_pair = self::instantiate_site_meta(
			array(
				'site_description' => $prompt,
			),
			'font_pair'
		);

		if ( isset( $color_palette['error'] ) || is_wp_error( $color_palette ) ) {
			$color_palette = array(
				array(
					'name'                 => 'Tropical Dawn',
					'base'                 => '#F0F0F0',
					'contrast'             => '#333333',
					'primary'              => '#09728C',
					'secondary'            => '#C79E10',
					'tertiary'             => '#F5EBB8',
					'header_background'    => '#09728C',
					'header_foreground'    => '#F5EBB8',
					'header_titles'        => '#F5EBB8',
					'secondary_background' => '#09728C',
					'secondary_foreground' => '#F5EBB8',
				),
				array(
					'name'                 => 'Earthy Delight',
					'base'                 => '#EAE2D6',
					'contrast'             => '#2E2E2E',
					'primary'              => '#D19858',
					'secondary'            => '#A1623B',
					'tertiary'             => '#704238',
					'header_background'    => '#D19858',
					'header_foreground'    => '#EAE2D6',
					'header_titles'        => '#EAE2D6',
					'secondary_background' => '#A1623B',
					'secondary_foreground' => '#EAE2D6',
				),
				array(
					'name'                 => 'Cool Breeze',
					'base'                 => '#D9E4E7',
					'contrast'             => '#1B1B1B',
					'primary'              => '#3C7A89',
					'secondary'            => '#5E9EA4',
					'tertiary'             => '#81BFC5',
					'header_background'    => '#3C7A89',
					'header_foreground'    => '#D9E4E7',
					'header_titles'        => '#D9E4E7',
					'secondary_background' => '#5E9EA4',
					'secondary_foreground' => '#D9E4E7',
				),
				array(
					'name'                 => 'Warm Comfort',
					'base'                 => '#F4E1D2',
					'contrast'             => '#272727',
					'primary'              => '#D83367',
					'secondary'            => '#F364A2',
					'tertiary'             => '#FEA5E2',
					'header_background'    => '#D83367',
					'header_foreground'    => '#F4E1D2',
					'header_titles'        => '#F4E1D2',
					'secondary_background' => '#F364A2',
					'secondary_foreground' => '#F4E1D2',
				),
				array(
					'name'                 => 'Classic Elegance',
					'base'                 => '#EDEDED',
					'contrast'             => '#1C1C1C',
					'primary'              => '#A239CA',
					'secondary'            => '#4717F6',
					'tertiary'             => '#E7DFDD',
					'header_background'    => '#A239CA',
					'header_foreground'    => '#EDEDED',
					'header_titles'        => '#EDEDED',
					'secondary_background' => '#4717F6',
					'secondary_foreground' => '#EDEDED',
				),
			);
		}

		if ( isset( $font_pair['error'] ) || is_wp_error( $font_pair ) ) {
			$font_pair = array(
				array(
					'aesthetics'    => 'modern',
					'fonts_heading' => 'Arial',
					'fonts_content' => 'Times New Roman',
					'spacing'       => 6,
					'radius'        => 4,
				),
				array(
					'aesthetics'    => 'vintage',
					'fonts_heading' => 'Courier New',
					'fonts_content' => 'Georgia',
					'spacing'       => 5,
					'radius'        => 3,
				),
				array(
					'aesthetics'    => 'minimalist',
					'fonts_heading' => 'Verdana',
					'fonts_content' => 'Tahoma',
					'spacing'       => 7,
					'radius'        => 2,
				),
				array(
					'aesthetics'    => 'retro',
					'fonts_heading' => 'Lucida Console',
					'fonts_content' => 'Palatino Linotype',
					'spacing'       => 6,
					'radius'        => 5,
				),
				array(
					'aesthetics'    => 'typographic',
					'fonts_heading' => 'Impact',
					'fonts_content' => 'Comic Sans MS',
					'spacing'       => 5,
					'radius'        => 3,
				),
			); // set default value
		}

		$default_design = array(
				'name'          => 'Modern Foodie',
				'style'         => array(
					'aesthetics'    => 'modern',
					'fonts_heading' => 'Arial',
					'fonts_content' => 'Times New Roman',
					'spacing'       => 6,
					'radius'        => 4,
				),
				'color_palette' => array(
					'base'                 => '#F0F0F0',
					'contrast'             => '#333333',
					'primary'              => '#09728C',
					'secondary'            => '#C79E10',
					'tertiary'             => '#F5EBB8',
					'header_background'    => '#09728C',
					'header_foreground'    => '#F5EBB8',
					'header_titles'        => '#F5EBB8',
					'secondary_background' => '#09728C',
					'secondary_foreground' => '#F5EBB8',
				),
			);

		return array(
			'design' => $default_design,
			'colorPalettes' => $color_palette,
			'designStyles' => $font_pair,
		);
	}

}
