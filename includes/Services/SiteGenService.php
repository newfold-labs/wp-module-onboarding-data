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
		return true;
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
	 * Gets the preview homepages
	 *
	 * @return array
	 */

	public static function generate_homepages($site_description, $content_style, $target_audience, $regenerate = false) {
        // Fetch homepages using the SiteGen::get_home_pages method
        $home_pages = SiteGen::get_home_pages(
            $site_description,
            $content_style,
            $target_audience,
            $regenerate
        );

        // Process the homepages
        $processed_home_pages = self::process_homepages_response($home_pages);

        // Update the option with processed homepages
        update_option(Options::get_option_name( Options::get_option_name( 'sitegen_homepages' ) ), $processed_home_pages);

        return $processed_home_pages;
    }

	/**
	 * Toggles the favourite status of a homepage
	 *
	 * @param string $slug The slug of the homepage to toggle
	 * @return array Response message
	 */

	public static function toggle_favourite_homepage($slug) {
        $homepages = get_option(Options::get_option_name( 'sitegen_homepages' ), []);
		$homepageFound = false;
       
		foreach ($homepages as &$homepage) {
			if ($homepage['slug'] === $slug) {
				$homepage['isFavourited'] = !$homepage['isFavourited'];
				$homepageFound = true;
				break;
			}
		}

		if ($homepageFound) {
			update_option(Options::get_option_name('sitegen_homepages'), $homepages);
		}
		
		$message = $homepageFound ? 'Favorite status updated' : 'Homepage for this slug not found';
    	return ['message' => $message];
    }

	/**
	 * Regenerate previews for favourited homepages
	 *
	 * @return array
	 */
	public static function handle_favorite_regeneration($regenerateSlug, $regenerateColorPalattes) {
        $existing_homepages = get_option(Options::get_option_name( 'sitegen_homepages' ), []);
        $favorite_regenerate_homepage = array_filter($existing_homepages, function ($homepage) use ($regenerateSlug) {
            return $homepage['slug'] === $regenerateSlug;
        });

        if (!empty($favorite_regenerate_homepage)) {
            $processed_homepage = self::process_favorited_regenerate($favorite_regenerate_homepage, $regenerateColorPalattes);
            $existing_homepages[] = $processed_homepage[0];
            update_option(Options::get_option_name( 'sitegen_homepages' ), $existing_homepages);

            return $existing_homepages;
        }

		return new \WP_Error(
			'nfd_onboarding_error',
			__( 'The favorited homepage could not be found for regeneration.', 'wp-module-onboarding' ),
			array(
				'status' => 404,
			)
		);
    }

	/**
	 * Regenerate previews homepages
	 *
	 * @return array
	 */
	public static function handle_regular_regeneration($site_description, $content_style, $target_audience) {
        $existing_homepages = get_option(Options::get_option_name( 'sitegen_homepages' ), []);
        $regenerated_homepages = get_option(Options::get_option_name( 'sitegen_regenerate_homepages' ), []);

        if (!empty($regenerated_homepages)) {
            $regenerated_item = array_shift($regenerated_homepages);
            $existing_homepages[] = $regenerated_item;
            update_option(Options::get_option_name( 'sitegen_regenerate_homepages' ), $regenerated_homepages);
        } else {
            $home_pages = SiteGen::get_home_pages($site_description, $content_style, $target_audience, true);
            $regenerated_homepages = self::process_homepages_response($home_pages);
            update_option(Options::get_option_name( 'sitegen_regenerate_homepages' ), $regenerated_homepages);
            $regenerated_item = array_shift($regenerated_homepages);
            $existing_homepages[] = $regenerated_item;
        }

        update_option(Options::get_option_name( 'sitegen_homepages' ), $existing_homepages);
        return $existing_homepages;
    }

	/**
	 * processes the Homepages response structure for homepages
	 *
	 * @return array
	 */
	public static function process_homepages_response(
		$home_pages,
		$regenerate = false,
		$regenerateSlug = '',
		$regenerateColorPalettes = null,
	){
		$versions = [];
		// Fetch the color palette data from the options table.
		$color_palettes = get_option('nfd-ai-site-gen-colorpalette');

		// Decode the color palettes if it's not an array (assuming it's a JSON string).
		if (!is_array($color_palettes)) {
			$color_palettes = json_decode($color_palettes, true);
		}

		// Retrieve the existing homepages to find the last version number.
		$existing_homepages = get_option(Options::get_option_name( 'sitegen_homepages' ), []);
		$last_version_number = self::get_last_version_number($existing_homepages);
		$version_number = $last_version_number + 1;

		foreach ($home_pages as $key => $blocks) {
			if (!is_array($blocks)) {
				continue;
			}

			$filtered_blocks = array_filter($blocks, function($value) {
				return !is_null($value);
			});

			$content = implode('', $filtered_blocks);

			// Select a random palette and check against the parent's palette.
			$palette_index = array_rand($color_palettes['colorpalette']);
			$selected_palette = self::transform_palette($color_palettes['colorpalette'][$palette_index], $palette_index);

			// If regeneration is true and the selected palette matches the parent's palette, reselect.
			if ($regenerate && $regenerateColorPalettes) {
				while ($selected_palette == $regenerateColorPalettes && count($color_palettes['colorpalette']) > 1) {
					$palette_index = array_rand($color_palettes['colorpalette']);
					$selected_palette = self::transform_palette($color_palettes['colorpalette'][$palette_index], $palette_index);
				}
			}

			$version_info = [
				"slug" => "version" . $version_number,
				"title" => "Version " . $version_number,
				"isFavourited" => false,
				"content" => $content,
				"color" => $selected_palette
			];

			$versions[] = $version_info;
			$version_number++;
		}

		return $versions;
	}

	/**
	 * processes the Homepages response structure for favourited prviews
	 *
	 * @return array
	 */
	public static function process_favorited_regenerate(
		$home_pages,
		$regenerateColorPalattes
	){
		$versions = [];
		// Fetch the color palette data from the options table.
		$color_palettes = get_option('nfd-ai-site-gen-colorpalette');

		// Decode the color palettes if it's not an array (assuming it's a JSON string).
		if (!is_array($color_palettes)) {
			$color_palettes = json_decode($color_palettes, true);
		}

		// Retrieve the existing homepages to find the last version number.
		$existing_homepages = get_option(Options::get_option_name( 'sitegen_homepages' ), []);

		// Select a random palette and check against the parent's palette.
		$palette_index = array_rand($color_palettes['colorpalette']);
		$selected_palette = self::transform_palette($color_palettes['colorpalette'][$palette_index], $palette_index);

		// If regeneration is true and the selected palette matches the parent's palette, reselect.
		if ($regenerateColorPalattes) {
			while ($selected_palette == $regenerateColorPalattes && count($color_palettes['colorpalette']) > 1) {
				$palette_index = array_rand($color_palettes['colorpalette']);
				$selected_palette = self::transform_palette($color_palettes['colorpalette'][$palette_index], $palette_index);
			}
		}

		$parent_favorited_homepage = current($home_pages);
		$version_info = [
			"slug" => $parent_favorited_homepage['slug'].'-copy',
			"title" => $parent_favorited_homepage['title'].' (Copy)',
			"isFavourited" => false,
			"content" => $parent_favorited_homepage['content'],
			"color" => $selected_palette
		];

		$versions[] = $version_info;

		return $versions;
	}

	/**
	 * Get the last version number to increment excluiding the (copy) versions
	 *
	 * @return array
	 */
	public static function get_last_version_number($homepages) {
		// Initialize to zero, assuming there are no versions yet.
		$last_version_number = 0;

		// Loop through the homepages to find the highest version number.
		foreach ($homepages as $homepage) {
			// Extract the number from the slug (assuming slug is like "version3").
			if (preg_match('/version(\d+)/', $homepage['slug'], $matches)) {
				$version_num = intval($matches[1]);
				if ($version_num > $last_version_number) {
					$last_version_number = $version_num;
				}
			}
		}

		return $last_version_number;
	}

	/**
	 * transform the color palatte structure for response
	 *
	 * @return array
	 */
	public static function transform_palette($palette, $palette_index) {
		$palette_name = "palette" . ($palette_index + 1);
		$transformed_palette = [
			"slug" => $palette_name,
			"palette" => array_map(function($key, $value) {
				return [
					"slug" => $key,
					"title" => ucfirst(str_replace('_', ' ', $key)),
					"color" => $value
				];
			}, array_keys($palette), $palette)
		];

		return $transformed_palette;
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

}
