<?php

namespace NewfoldLabs\WP\Module\Performance\Images;

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\Performance\Permissions;
use NewfoldLabs\WP\Module\Performance\Images\RestApi\RestApi;
use NewfoldLabs\WP\Module\Performance\Images\ImageRewriteHandler;

/**
 * Manages the initialization of image optimization settings and listeners.
 */
class ImageManager {

	/**
	 * Constructor to initialize the ImageManager.
	 *
	 * Registers settings and conditionally initializes related services.
	 *
	 * @param Container $container Dependency injection container.
	 */
	public function __construct( Container $container ) {
		$this->initialize_settings();
		$this->initialize_services( $container );
	}

	/**
	 * Initializes the ImageSettings class to register settings.
	 */
	private function initialize_settings() {
		new ImageSettings();
	}

	/**
	 * Initializes conditional services based on settings and environment.
	 *
	 * @param Container $container Dependency injection container.
	 */
	private function initialize_services( Container $container ) {
		$this->initialize_upload_listener();
		$this->maybe_initialize_lazy_loader();
		$this->maybe_initialize_bulk_optimizer();
		$this->maybe_initialize_rest_api();
		$this->maybe_initialize_marker();
		$this->maybe_initialize_image_rewrite_handler( $container );
		$this->maybe_initialize_image_limit_banner( $container );
	}

	/**
	 * Initializes the ImageUploadListener if auto-optimization is enabled.
	 */
	private function initialize_upload_listener() {
		new ImageUploadListener( ImageSettings::is_auto_delete_enabled() );
	}

	/**
	 * Initializes the ImageLazyLoader if lazy loading is enabled.
	 */
	private function maybe_initialize_lazy_loader() {
		if ( ImageSettings::is_optimization_enabled() && ImageSettings::is_lazy_loading_enabled() ) {
			new ImageLazyLoader();
		}
	}

	/**
	 * Initializes the ImageBulkOptimizer if bulk optimization is enabled and user is an admin.
	 */
	private function maybe_initialize_bulk_optimizer() {
		if ( Permissions::is_authorized_admin() && ImageSettings::is_bulk_optimization_enabled() ) {
			new ImageBulkOptimizer();
		}
	}

	/**
	 * Initializes the REST API routes if accessed via REST and user is an admin.
	 */
	private function maybe_initialize_rest_api() {
		if ( Permissions::rest_is_authorized_admin() ) {
			new RestApi();
		}
	}

	/**
	 * Initializes the ImageOptimizedMarker if image optimization is enabled.
	 */
	private function maybe_initialize_marker() {
		if ( ImageSettings::is_optimization_enabled() ) {
			new ImageOptimizedMarker();
		}
	}

	/**
	 * Initializes the ImageRewriteHandler for managing WebP redirects if the server is Apache.
	 *
	 * @param Container $container Dependency injection container.
	 */
	private function maybe_initialize_image_rewrite_handler( Container $container ) {
		if ( Permissions::rest_is_authorized_admin()
		&& $container->has( 'isApache' )
		&& $container->get( 'isApache' ) ) {
			new ImageRewriteHandler();
		}
	}

	/**
	 * Conditionally initializes the Image Limit Banner in the WordPress admin area.
	 *
	 * @param Container $container Dependency injection container.
	 */
	private function maybe_initialize_image_limit_banner( $container ) {
		if ( ImageSettings::is_optimization_enabled() && Permissions::is_authorized_admin() ) {
			new ImageLimitBanner( $container );
		}
	}
}
