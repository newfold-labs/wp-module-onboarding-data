<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Themes;

use NewfoldLabs\WP\Module\Onboarding\Data\Themes;

/**
 * Contains custom font palettes for a given theme.
 */
final class Fonts {

	/**
	 * This contains the different font variations for the theme.
	 *
	 * @var string
	 */
	protected static function get_theme_fonts() {
		return array(
			'yith-wonder' => array(
				'modern-approachable'            => array(
					'label'   => __( 'Modern & approachable', 'wp-module-onboarding-data' ),
					'matches' => 'yith-wonder/theme-json',
					'slugs'   => array( 'mulish', 'poppins' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--mulish)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--poppins)',
								),
							),
						),
					),
				),
				'strong-sleek'                   => array(
					'label'   => __( 'Strong & sleek', 'wp-module-onboarding-data' ),
					'matches' => 'yith-wonder/styles/01-blue-shades',
					'slugs'   => array( 'raleway', 'oswald' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--raleway)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--oswald)',
								),
							),
						),
					),
				),
				'stately-elevated'               => array(
					'label'   => __( 'Stately & elevated', 'wp-module-onboarding-data' ),
					'matches' => 'yith-wonder/styles/02-pink-shades',
					'slugs'   => array( 'source-sans-pro', 'playfair' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--source-sans-pro)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--playfair)',
								),
							),
						),
					),
				),
				'typewriter-crisp-midcentury'    => array(
					'label'   => __( 'Typewriter & crisp midcentury', 'wp-module-onboarding-data' ),
					'matches' => 'yith-wonder/styles/03-orange-shades',
					'slugs'   => array( 'jost', 'solway' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--jost)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--solway)',
								),
							),
						),
					),
				),
				'refined-traditional-newsletter' => array(
					'label'   => __( 'Refined traditional newsletter', 'wp-module-onboarding-data' ),
					'matches' => 'yith-wonder/styles/04-black-shades',
					'slugs'   => array( 'jost', 'merriweather' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--jost)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--merriweather)',
								),
							),
						),
					),
				),
				'bold-stamp-slab'                => array(
					'label'   => __( 'Bold stamp & slab', 'wp-module-onboarding-data' ),
					'matches' => 'yith-wonder/styles/05-red-shades',
					'slugs'   => array( 'changa-one', 'roboto-slab' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--roboto-slab)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--changa-one)',
								),
							),
						),
					),
				),
				'fast-simple'                    => array(
					'label'   => __( 'Fast & Simple', 'wp-module-onboarding-data' ),
					'matches' => 'newfold/onboarding-01',
					'slugs'   => array( 'system' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--system)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--system)',
								),
							),
						),
					),
				),
				'timeless-traditional'           => array(
					'label'   => __( 'Timeless & Traditional', 'wp-module-onboarding-data' ),
					'matches' => 'newfold/onboarding-02',
					'slugs'   => array( 'serif' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--serif)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--serif)',
								),
							),
						),
					),
				),
				'sleek-sophisticated'            => array(
					'label'   => __( 'Sleek & Sophisticated', 'wp-module-onboarding-data' ),
					'matches' => 'newfold/onboarding-03',
					'slugs'   => array( 'dm-sans' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--dm-sans)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--dm-sans)',
								),
							),
						),
					),
				),
				'clear-crisp'                    => array(
					'label'   => __( 'Clear & Crisp', 'wp-module-onboarding-data' ),
					'matches' => 'newfold/onboarding-04',
					'slugs'   => array( 'inter' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--inter)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--inter)',
								),
							),
						),
					),
				),
				'retro-classy'                   => array(
					'label'   => __( 'Retro & Classy', 'wp-module-onboarding-data' ),
					'matches' => 'newfold/onboarding-05',
					'slugs'   => array( 'league-spartan' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--league-spartan)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--league-spartan)',
								),
							),
						),
					),
				),
				'defined-solid'                  => array(
					'label'   => __( 'Defined & Solid', 'wp-module-onboarding-data' ),
					'matches' => 'newfold/onboarding-06',
					'slugs'   => array( 'roboto-slab' ),
					'styles'  => array(
						'typography' => array(
							'fontFamily' => 'var(--wp--preset--font-family--roboto-slab)',
						),
						'blocks'     => array(
							'core/heading' => array(
								'typography' => array(
									'fontFamily' => 'var(--wp--preset--font-family--roboto-slab)',
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * This returns the default font data for Sitegen customize sidebar.
	 *
	 * @var string
	 */
	public static function get_sitegen_font_data() {
		return array(
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
		);
	}

	/**
	 * This returns the default design data for Sitegen customize sidebar.
	 *
	 * @var string
	 */
	public static function get_sitegen_default_design_data() {
		return array(
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
	}

	/**
	 * Retrieves the active theme font variations.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_fonts_from_theme() {
		$active_theme = Themes::get_active_theme();
		$theme_fonts  = self::get_theme_fonts();
		return isset( $theme_fonts[ $active_theme ] ) ? $theme_fonts[ $active_theme ] : false;
	}
}
