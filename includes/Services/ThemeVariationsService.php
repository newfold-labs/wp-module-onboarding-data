<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

class ThemeVariationsService {
	/**
	 * Translate decoded json file.
	 *
	 * @param string $theme_json Theme content.
	 * @param string $domain Domain type.
	 * @return string
	 */
	private static function translate( $theme_json, $domain = 'default' ) {
		$i18n_schema = wp_json_file_decode( __DIR__ . '/theme-i18n.json' );

		return translate_settings_using_i18n_schema( $i18n_schema, $theme_json, $domain );
	}

	/**
	 * Translate the json decoded HTML Files and retrieve all the Theme Style Variations.
	 *
	 * @return array
	 */
	private static function get_style_variations(): array {
		$variations     = array();
		$base_directory = get_stylesheet_directory() . '/styles';
		if ( is_dir( $base_directory ) ) {
			$nested_files      = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $base_directory ) );
			$nested_html_files = iterator_to_array( new \RegexIterator( $nested_files, '/^.+\.json$/i', \RecursiveRegexIterator::GET_MATCH ) );
			ksort( $nested_html_files );
			foreach ( $nested_html_files as $path => $file ) {
				$decoded_file = wp_json_file_decode( $path, array( 'associative' => true ) );
				if ( is_array( $decoded_file ) ) {
					$translated = self::translate( $decoded_file, wp_get_theme()->get( 'TextDomain' ) );
					$variation  = ( new \WP_Theme_JSON( $translated ) )->get_data();
					if ( empty( $variation['title'] ) ) {
						$variation['title'] = basename( $path, '.json' );
					}
					$variations[] = $variation;
				}
			}
		}
		return $variations;
	}

	/**
	 * Retrieves the active themes variations.
	 *
	 * @return array
	 */
	public static function get_theme_variations(): array {
		$active_variation              = self::get_active_theme_variation();
		$active_variation_global_style = array(
			'id'       => 0,
			'title'    => 'Default',
			'version'  => $active_variation['version'],
			'settings' => $active_variation['settings'],
			'styles'   => $active_variation['styles'],
		);

		return array_merge(
			array( $active_variation_global_style ),
			self::get_style_variations()
		);
	}

	/**
	 * Retrieves the active theme variation.
	 *
	 * @return array
	 */
	public static function get_active_theme_variation(): array {
		$active_variation = \WP_Theme_JSON_Resolver::get_theme_data()->get_data();
		return $active_variation;
	}

	/**
	 * Retrieves the active theme variation global style.
	 *
	 * @return array
	 */
	public static function get_active_theme_variation_global_style(): array {
		$active_variation = self::get_active_theme_variation();
		return array(
			'id'       => 0,
			'title'    => 'Default',
			'version'  => $active_variation['version'],
			'settings' => $active_variation['settings'],
			'styles'   => $active_variation['styles'],
		);
	}
}
