<?php

namespace NewfoldLabs\WP\Module\Onboarding\Data\Services;

/**
 * Contains functionalities related to generating child themes and theme variations.
 */
class ThemeGeneratorService {
		/**
		 * Connect to the WordPress filesystem.
		 *
		 * @return boolean
		 */
	public static function connect_to_filesystem() {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// We want to ensure that the user has direct access to the filesystem.
		$access_type = \get_filesystem_method();
		if ( 'direct' !== $access_type ) {
			return false;
		}

		$creds = \request_filesystem_credentials( site_url() . '/wp-admin', '', false, false, array() );

		if ( ! \WP_Filesystem( $creds ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Activates a given theme.
	 *
	 * @param string $theme_slug WordPress slug for theme
	 *
	 * @return void
	 */
	public static function activate_theme( $theme_slug ) {
		\switch_theme( $theme_slug );
	}

	/**
	 * Write the child theme to the themes directory.
	 *
	 * @param array $child_theme_data Child Theme Data
	 * @var string  parent_theme_slug
	 * @var string  child_theme_slug
	 * @var string  parent_theme_dir
	 * @var string  child_theme_dir
	 * @var string  child_theme_json
	 * @var string  part_patterns
	 *
	 * @return string|boolean
	 */
	public static function write_child_theme( $child_theme_data ) {
		$child_directory_created = self::create_directory( $child_theme_data['child_theme_dir'] );
		if ( ! $child_directory_created ) {
			return 'Error creating child directory.';
		}

		$theme_json_written = self::write_theme_json(
			$child_theme_data['child_theme_dir'],
			$child_theme_data['child_theme_json']
		);
		if ( ! $theme_json_written ) {
			return 'Error writing theme.json.';
		}

		if ( ! empty( $child_theme_data['part_patterns'] ) ) {
			$template_parts_written = self::write_template_parts(
				$child_theme_data['child_theme_dir'],
				$child_theme_data['part_patterns']
			);
			if ( ! $template_parts_written ) {
				return 'Error writing template part.';
			}
		}

		$child_stylesheet_written = self::write_child_stylesheet(
			$child_theme_data['child_theme_stylesheet_comment'],
			$child_theme_data['child_theme_dir']
		);
		if ( ! $child_stylesheet_written ) {
			return 'Error writing stylesheet.';
		}

		$generate_screenshot = self::generate_screenshot(
			$child_theme_data['theme_screenshot'],
			$child_theme_data['theme_screenshot_dir'],
			$child_theme_data['child_theme_dir']
		);
		if ( ! $generate_screenshot ) {
			return 'Error generating screenshot';
		}

		return true;
	}

		/**
		 * Creates a directory if necessary.
		 *
		 * @param string $dir Directory
		 *
		 * @return boolean
		 */
	public static function create_directory( $dir ) {
		global $wp_filesystem;

		if ( ! $wp_filesystem->exists( $dir ) ) {
			return $wp_filesystem->mkdir( $dir );
		}

		return true;
	}

	/**
	 * Writes $theme_json to a theme's theme.json file.
	 *
	 * @param string $theme_dir Theme Directory
	 * @param string $theme_json Theme json content
	 *
	 * @return boolean
	 */
	public static function write_theme_json( $theme_dir, $theme_json ) {
		return self::write_to_filesystem( $theme_dir . '/theme.json', $theme_json );
	}

		/**
		 * Writes HTML template parts to the theme's parts directory.
		 *
		 * @param string $theme_dir Theme Directory
		 * @param array  $part_patterns HTML Template Part
		 *
		 * @return boolean
		 */
	public static function write_template_parts( $theme_dir, $part_patterns ) {
		global $wp_filesystem;

		if ( ! $wp_filesystem->exists( $theme_dir . '/parts' ) ) {
			$parts_directory_created = self::create_directory( $theme_dir . '/parts' );
			if ( ! $parts_directory_created ) {
				return false;
			}
		}
		foreach ( $part_patterns as $part => $pattern ) {
			$status = self::write_to_filesystem( $theme_dir . "/parts/{$part}.html", $pattern );
			if ( ! $status ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Writes style.css for the child theme.
	 *
	 * @param string $child_theme_stylesheet_comment Stylesheet comment of Child Theme
	 * @param string $child_theme_dir Child Theme Directory
	 *
	 * @return boolean
	 */
	public static function write_child_stylesheet( $child_theme_stylesheet_comment, $child_theme_dir ) {
		return self::write_to_filesystem( $child_theme_dir . '/style.css', $child_theme_stylesheet_comment );
	}


	/**
	 * Writes content to the specified file.
	 *
	 * @param string $file Specific File where $content is to be written
	 * @param string $content Content to write to the $file
	 *
	 * @return boolean
	 */
	public static function write_to_filesystem( $file, $content ) {
		global $wp_filesystem;

		return $wp_filesystem->put_contents(
			$file,
			$content,
			FS_CHMOD_FILE // predefined mode settings for WP files
		);
	}

		/**
		 * Copy parent's screenshot.png to the child theme directory.
		 *
		 * @param string $screenshot The screenshot data - base64 encoded.
		 * @param string $src_dir The Source Directory
		 * @param string $child_theme_dir Child Theme Directory
		 *
		 * @return boolean
		 */
	public static function generate_screenshot( $screenshot, $src_dir, $child_theme_dir ) {
		global $wp_filesystem;

		$screenshot_files = array( '/screenshot.png', '/screenshot.jpg' );
		if ( $screenshot ) {
			$screenshot                  = base64_decode( $screenshot );
			$child_theme_screenshot_file = $child_theme_dir . '/screenshot.png';
			return self::write_to_filesystem( $child_theme_screenshot_file, $screenshot );
		}
		foreach ( $screenshot_files as $key => $screenshot_file ) {
			$child_theme_screenshot_file = $child_theme_dir . $screenshot_file;
			$screenshot_file             = $src_dir . $screenshot_file;
			if ( $wp_filesystem->exists( $screenshot_file ) ) {
				break;
			}
		}

		if ( $wp_filesystem->exists( $child_theme_screenshot_file ) ) {
			$wp_filesystem->delete( $child_theme_screenshot_file );
		}

		return $wp_filesystem->copy(
			$screenshot_file,
			$child_theme_screenshot_file
		);
	}

		/**
		 * Retrieve Site Url Hash Value
		 *
		 * @param integer $length hash length
		 *
		 * @return string
		 */
	public static function get_site_url_hash( $length = 8 ) {
		return substr( hash( 'sha256', site_url() ), 0, $length );
	}
}
