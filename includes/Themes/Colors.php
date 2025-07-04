<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Themes;

use NewfoldLabs\WP\Module\Onboarding\Data\Themes;

/**
 * Contains custom color palettes for a given theme.
 */
final class Colors {

	/**
	 * This contains the different color variations for the theme.
	 *
	 * @var string
	 */
	protected static function get_theme_colors() {
		return array(
			'yith-wonder' => array(
				'tailored'               => array(
					'calm'         => array(
						'header-background'    => '#1A4733',
						'header-foreground'    => '#FFFFFF',
						'header-titles'        => '#FFFFFF',
						'secondary-background' => '#1A4733',
						'secondary-foreground' => '#FFF',
						'tertiary'             => '#C7DBFF',
						'secondary'            => '#344A77',
						'primary'              => '#1A4733',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Calm', 'wp-module-onboarding-data' ),
					),
					'cool'         => array(
						'header-background'    => '#C7DBFF',
						'header-foreground'    => '#21447B',
						'header-titles'        => '#21447B',
						'secondary-background' => '#C7DBFF',
						'secondary-foreground' => '#21447B',
						'tertiary'             => '#C7DBFF',
						'secondary'            => '#3764B4',
						'primary'              => '#21447B',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Cool', 'wp-module-onboarding-data' ),
					),
					'warm'         => array(
						'header-background'    => '#FDE5D0',
						'header-foreground'    => '#7A3921',
						'header-titles'        => '#7A3921',
						'secondary-background' => '#FDE5D0',
						'secondary-foreground' => '#7A3921',
						'tertiary'             => '#FFEDED',
						'secondary'            => '#B97040',
						'primary'              => '#7A3921',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Warm', 'wp-module-onboarding-data' ),
					),
					'radiant'      => array(
						'header-background'    => '#63156A',
						'header-foreground'    => '#E3F7FF',
						'header-titles'        => '#E3F7FF',
						'secondary-background' => '#781980',
						'secondary-foreground' => '#E3F7FF',
						'tertiary'             => '#C7F0FF',
						'secondary'            => '#64288C',
						'primary'              => '#63156A',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Radiant', 'wp-module-onboarding-data' ),
					),
					'bold'         => array(
						'header-background'    => '#FFD7F1',
						'header-foreground'    => '#09857C',
						'header-titles'        => '#09857C',
						'secondary-background' => '#ffddf3',
						'secondary-foreground' => '#09857C',
						'tertiary'             => '#F2A3D6',
						'secondary'            => '#076D66',
						'primary'              => '#09857C',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Bold', 'wp-module-onboarding-data' ),
					),
					'retro'        => array(
						'header-background'    => '#096385',
						'header-foreground'    => '#F2E6A2',
						'header-titles'        => '#F2E6A2',
						'secondary-background' => '#096385',
						'secondary-foreground' => '#F2E6A2',
						'tertiary'             => '#F2E6A2',
						'secondary'            => '#BE9E00',
						'primary'              => '#096385',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Retro', 'wp-module-onboarding-data' ),
					),
					'professional' => array(
						'header-background'    => '#6D8258',
						'header-foreground'    => '#F5FAFF',
						'header-titles'        => '#D2E0F5',
						'secondary-background' => '#6D8258',
						'secondary-foreground' => '#F5FAFF',
						'tertiary'             => '#d6e4f9',
						'secondary'            => '#405F1C',
						'primary'              => '#558320',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Professional', 'wp-module-onboarding-data' ),
					),
					'crisp'        => array(
						'header-background'    => '#ccc',
						'header-foreground'    => '#333',
						'header-titles'        => '#234',
						'secondary-background' => '#ccc',
						'secondary-foreground' => '#333',
						'tertiary'             => '#777',
						'secondary'            => '#17222E',
						'primary'              => '#223344',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Crisp', 'wp-module-onboarding-data' ),
					),
					'polished'     => array(
						'header-background'    => '#313131',
						'header-foreground'    => '#fff',
						'header-titles'        => '#6B69EA',
						'secondary-background' => '#444',
						'secondary-foreground' => '#ddd',
						'tertiary'             => '#313131',
						'secondary'            => '#6B69EA',
						'primary'              => '#5100FA',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Polished', 'wp-module-onboarding-data' ),
					),
					'nightowl'     => array(
						'header-background'    => '#06080A',
						'header-foreground'    => '#fff',
						'header-titles'        => '#FAAA14',
						'secondary-background' => '#0A0C0E',
						'secondary-foreground' => '#fff',
						'tertiary'             => '#FFDFA3',
						'secondary'            => '#06080A',
						'primary'              => '#B97900',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Nightowl', 'wp-module-onboarding-data' ),
					),
					'subtle'       => array(
						'header-background'    => '#C7ADBB',
						'header-foreground'    => '#5A3C4B',
						'header-titles'        => '#5A3C4B',
						'secondary-background' => '#C7ADBB',
						'secondary-foreground' => '#5A3C4B',
						'tertiary'             => '#D4C9CF',
						'secondary'            => '#57203c',
						'primary'              => '#5A3C4B',
						'base'                 => '#FFFFFF',
						'name'                 => __( 'Subtle', 'wp-module-onboarding-data' ),
					),
				),
				'custom-picker-grouping' => array(
					'base'     => array(
						'header-foreground',
						'header-titles',
						'secondary-foreground',
					),
					'tertiary' => array(
						'header-background',
						'secondary-background',
					),
				),
			),
		);
	}

	/**
	 * This returns default Color Palette data for Sitegen customize sidebar.
	 *
	 * @var string
	 */
	public static function get_sitegen_color_palette_data() {
		return array(
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

	/**
	 * Retrieves the active theme color variations.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_colors_from_theme() {
		$active_theme  = Themes::get_active_theme();
		$pattern_slugs = self::get_theme_colors()[ $active_theme ];

		if ( ! isset( $pattern_slugs ) ) {
			return new \WP_Error(
				__( 'Theme Colors not found', 'wp-module-onboarding-data' ),
				__( 'No WordPress Colors are available for this theme.', 'wp-module-onboarding-data' ),
				array( 'status' => 404 )
			);
		}

		return $pattern_slugs;
	}
}
