<?php

namespace NewfoldLabs\WP\Module\Performance\Images;

/**
 * Manages the registration and sanitization of image optimization settings.
 */
class ImageSettings {
	/**
	 * The setting key for image optimization.
	 */
	private const SETTING_KEY = 'nfd_image_optimization';

	/**
	 * Default settings for image optimization.
	 *
	 * @var array
	 */
	private const DEFAULT_SETTINGS = array(
		'enabled'                            => true,
		'bulk_optimization'                  => true,
		'prefer_optimized_image_when_exists' => true,
		'auto_optimized_uploaded_images'     => array(
			'enabled'                    => true,
			'auto_delete_original_image' => false,
		),
		'lazy_loading'                       => array(
			'enabled' => true,
		),
		'banned_status'                      => false,
		'monthly_usage'                      => array(
			'monthlyRequestCount' => 0,
			'maxRequestsPerMonth' => 100000,
		),
	);

	/**
	 * Constructor to initialize the settings and the listener.
	 */
	public function __construct() {
		$this->register_settings();
		$this->initialize_settings();
	}

	/**
	 * Registers the `nfd_image_optimization` setting in WordPress.
	 */
	private function register_settings() {
		register_setting(
			'general',
			self::SETTING_KEY,
			array(
				'type'              => 'object',
				'description'       => __( 'Settings for NFD Image Optimization.', 'wp-module-performance' ),
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => self::DEFAULT_SETTINGS,
				'show_in_rest'      => array(
					'schema' => array(
						'type'                 => 'object',
						'properties'           => array(
							'enabled'           => array(
								'type'        => 'boolean',
								'description' => __( 'Enable image optimization.', 'wp-module-performance' ),
								'default'     => self::DEFAULT_SETTINGS['enabled'],
							),
							'prefer_optimized_image_when_exists' => array(
								'type'        => 'boolean',
								'description' => __( 'Prefer WebP format when it exists.', 'wp-module-performance' ),
								'default'     => self::DEFAULT_SETTINGS['prefer_optimized_image_when_exists'],
							),
							'auto_optimized_uploaded_images' => array(
								'type'        => 'object',
								'description' => __( 'Auto-optimized uploaded images settings.', 'wp-module-performance' ),
								'properties'  => array(
									'enabled' => array(
										'type'        => 'boolean',
										'description' => __( 'Automatically optimize uploaded images.', 'wp-module-performance' ),
										'default'     => self::DEFAULT_SETTINGS['auto_optimized_uploaded_images']['enabled'],
									),
									'auto_delete_original_image' => array(
										'type'        => 'boolean',
										'description' => __( 'Delete the original uploaded image after optimization.', 'wp-module-performance' ),
										'default'     => self::DEFAULT_SETTINGS['auto_optimized_uploaded_images']['auto_delete_original_image'],
									),
								),
							),
							'lazy_loading'      => array(
								'type'        => 'object',
								'description' => __( 'Settings for lazy loading.', 'wp-module-performance' ),
								'properties'  => array(
									'enabled' => array(
										'type'        => 'boolean',
										'description' => __( 'Enable lazy loading.', 'wp-module-performance' ),
										'default'     => self::DEFAULT_SETTINGS['lazy_loading']['enabled'],
									),
								),
							),
							'bulk_optimization' => array(
								'type'        => 'boolean',
								'description' => __( 'Enable bulk optimization of images.', 'wp-module-performance' ),
								'default'     => self::DEFAULT_SETTINGS['bulk_optimization'],
							),
							'banned_status'     => array(
								'type'        => 'boolean',
								'description' => __( 'Indicates if the site is banned from image optimization.', 'wp-module-performance' ),
								'default'     => self::DEFAULT_SETTINGS['banned_status'],
							),
							'monthly_usage'     => array(
								'type'        => 'object',
								'description' => __( 'Monthly usage statistics for image optimization.', 'wp-module-performance' ),
								'properties'  => array(
									'monthlyRequestCount' => array(
										'type'        => 'integer',
										'description' => __( 'Number of requests made this month.', 'wp-module-performance' ),
										'default'     => self::DEFAULT_SETTINGS['monthly_usage']['monthlyRequestCount'],
									),
									'maxRequestsPerMonth' => array(
										'type'        => 'integer',
										'description' => __( 'Maximum allowed requests per month.', 'wp-module-performance' ),
										'default'     => self::DEFAULT_SETTINGS['monthly_usage']['maxRequestsPerMonth'],
									),
								),
							),
						),
						'additionalProperties' => false,
					),
				),
			)
		);
	}

	/**
	 * Initializes the setting if it does not exist.
	 */
	private function initialize_settings() {
		$current_settings = get_option( self::SETTING_KEY, false );

		if ( false === $current_settings || ! is_array( $current_settings ) ) {
			add_option( self::SETTING_KEY, self::DEFAULT_SETTINGS );
		}
	}

	/**
	 * Sanitizes the `nfd_image_optimization` settings.
	 *
	 * @param array $settings The input settings.
	 * @return array The sanitized settings.
	 */
	public function sanitize_settings( $settings ) {
		$existing_settings = get_option( self::SETTING_KEY, array() );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return array(
			'enabled'                            => isset( $settings['enabled'] ) ? ! empty( $settings['enabled'] ) : ( ! empty( $existing_settings['enabled'] ) ),
			'prefer_optimized_image_when_exists' => isset( $settings['prefer_optimized_image_when_exists'] ) ? ! empty( $settings['prefer_optimized_image_when_exists'] ) : ( ! empty( $existing_settings['prefer_optimized_image_when_exists'] ) ),
			'auto_optimized_uploaded_images'     => array(
				'enabled'                    => isset( $settings['auto_optimized_uploaded_images']['enabled'] ) ? ! empty( $settings['auto_optimized_uploaded_images']['enabled'] ) : ( ! empty( $existing_settings['auto_optimized_uploaded_images']['enabled'] ) ),
				'auto_delete_original_image' => isset( $settings['auto_optimized_uploaded_images']['auto_delete_original_image'] ) ? ! empty( $settings['auto_optimized_uploaded_images']['auto_delete_original_image'] ) : ( ! empty( $existing_settings['auto_optimized_uploaded_images']['auto_delete_original_image'] ) ),
			),
			'lazy_loading'                       => array(
				'enabled' => isset( $settings['lazy_loading']['enabled'] ) ? ! empty( $settings['lazy_loading']['enabled'] ) : ( ! empty( $existing_settings['lazy_loading']['enabled'] ) ),
			),
			'bulk_optimization'                  => isset( $settings['bulk_optimization'] ) ? ! empty( $settings['bulk_optimization'] ) : ( ! empty( $existing_settings['bulk_optimization'] ) ),
			'banned_status'                      => isset( $settings['banned_status'] ) ? ! empty( $settings['banned_status'] ) : ( ! empty( $existing_settings['banned_status'] ) ),
			'monthly_usage'                      => array(
				'monthlyRequestCount' => isset( $settings['monthly_usage']['monthlyRequestCount'] ) ? (int) $settings['monthly_usage']['monthlyRequestCount'] : ( isset( $existing_settings['monthly_usage']['monthlyRequestCount'] ) ? (int) $existing_settings['monthly_usage']['monthlyRequestCount'] : 0 ),
				'maxRequestsPerMonth' => isset( $settings['monthly_usage']['maxRequestsPerMonth'] ) ? (int) $settings['monthly_usage']['maxRequestsPerMonth'] : ( isset( $existing_settings['monthly_usage']['maxRequestsPerMonth'] ) ? (int) $existing_settings['monthly_usage']['maxRequestsPerMonth'] : 100000 ),
			),
		);
	}

	/**
	 * Checks if image optimization is enabled.
	 *
	 * @return bool True if optimization is enabled, false otherwise.
	 */
	public static function is_optimization_enabled() {
		$settings = get_option( self::SETTING_KEY, self::DEFAULT_SETTINGS );
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Checks if auto-optimization for uploaded images is enabled.
	 *
	 * @return bool True if auto-optimization is enabled, false otherwise.
	 */
	public static function is_auto_optimization_enabled() {
		$settings = get_option( self::SETTING_KEY, self::DEFAULT_SETTINGS );
		return ! empty( $settings['auto_optimized_uploaded_images']['enabled'] );
	}

	/**
	 * Checks if auto-deletion of the original image is enabled.
	 *
	 * @return bool True if auto-deletion is enabled, false otherwise.
	 */
	public static function is_auto_delete_enabled() {
		$settings = get_option( self::SETTING_KEY, self::DEFAULT_SETTINGS );
		return ! empty( $settings['auto_optimized_uploaded_images']['auto_delete_original_image'] );
	}

	/**
	 * Checks if lazy loading is enabled.
	 *
	 * @return bool True if lazy loading is enabled, false otherwise.
	 */
	public static function is_lazy_loading_enabled() {
		$settings = get_option( self::SETTING_KEY, self::DEFAULT_SETTINGS );
		return ! empty( $settings['lazy_loading']['enabled'] );
	}

	/**
	 * Checks if bulk optimization is enabled.
	 *
	 * @return bool True if bulk optimization is enabled, false otherwise.
	 */
	public static function is_bulk_optimization_enabled() {
		$settings = get_option( self::SETTING_KEY, self::DEFAULT_SETTINGS );
		return ! empty( $settings['bulk_optimization'] );
	}

	/**
	 * Checks if WebP preference is enabled.
	 *
	 * @return bool True if WebP preference is enabled, false otherwise.
	 */
	public static function is_webp_preference_enabled() {
		$settings = get_option( self::SETTING_KEY, self::DEFAULT_SETTINGS );
		return ! empty( $settings['prefer_optimized_image_when_exists'] );
	}

	/**
	 * Checks if the site is banned from image optimization.
	 *
	 * @return bool True if the site is banned, false otherwise.
	 */
	public static function is_banned() {
		$settings = get_option( self::SETTING_KEY, self::DEFAULT_SETTINGS );
		return ! empty( $settings['banned_status'] );
	}

	/**
	 * Retrieves the monthly usage statistics for image optimization.
	 *
	 * @return array An array containing `monthlyRequestCount` and `maxRequestsPerMonth`.
	 */
	public static function get_monthly_usage() {
		$settings = get_option( self::SETTING_KEY, self::DEFAULT_SETTINGS );

		// Ensure monthly_usage exists and return default values if not set
		return isset( $settings['monthly_usage'] ) && is_array( $settings['monthly_usage'] )
		? $settings['monthly_usage']
		: array(
			'monthlyRequestCount' => 0,
			'maxRequestsPerMonth' => 100000,
		);
	}

	/**
	 * Retrieves the image optimization settings.
	 *
	 * @param bool $call_worker Whether to fetch the latest monthly usage from the worker. Default is true.
	 *
	 * @return array The current image optimization settings, including monthly usage and banned status.
	 */
	public static function get( $call_worker = true ) {
		$settings = get_option( self::SETTING_KEY, array() );

		if ( ! is_array( $settings ) ) {
			$settings = self::DEFAULT_SETTINGS;
		}

		if ( ! isset( $settings['banned_status'] ) ) {
			$settings['banned_status'] = self::is_banned();
		}

		if ( $call_worker && ( empty( $settings['monthly_usage'] ) || ! is_array( $settings['monthly_usage'] ) ) ) {
			$usage_data = ( new ImageService() )->get_monthly_usage_limit( true );
			if ( ! is_wp_error( $usage_data ) ) {
				$settings['monthly_usage'] = $usage_data;
				update_option( self::SETTING_KEY, $settings );
			} else {
				$settings['monthly_usage'] = isset( $settings['monthly_usage'] ) ? $settings['monthly_usage'] : array(
					'monthlyRequestCount' => 0,
					'maxRequestsPerMonth' => 100000,
				);
			}
		}

		return $settings;
	}

	/**
	 * Updates the image optimization settings.
	 *
	 * @param array $settings The new settings array.
	 *
	 * @return bool true if the settings were updated successfully, false otherwise.
	 */
	public static function update( $settings ) {
		$instance           = new self();
		$sanitized_settings = $instance->sanitize_settings( $settings );
		return update_option( self::SETTING_KEY, $sanitized_settings );
	}
}
