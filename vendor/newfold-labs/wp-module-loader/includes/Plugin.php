<?php

namespace NewfoldLabs\WP\ModuleLoader;

use WP_Forge\Fluent\Fluent;

/**
 * Plugin class.
 *
 * @property string $basename    Plugin basename
 * @property string $name        Plugin name
 * @property string $dir         Plugin directory
 * @property string $file        Plugin file
 * @property Fluent $headers     Plugin file headers
 * @property string $url         Plugin URL
 * @property string $version     Plugin version
 *
 * @method void file( string $file )
 */
class Plugin extends Fluent {

	public function __construct( $attributes = [] ) {
		parent::__construct( $attributes );
		if ( $this->has( 'file' ) ) {
			$this->setup( $this->file );
		}
	}

	public function __set( $key, $value ) {

		switch ( $key ) {
			case 'file':
				$this->setup( $value );

				return;
		}

		$this->set( $key, $value );

	}

	/**
	 * When the plugin file is set, collect all the info we can about the plugin.
	 *
	 * @param string $file
	 */
	protected function setup( $file ) {

		$this->set( 'file', $file );
		$this->set( 'dir', plugin_dir_path( $file ) );
		$this->set( 'url', plugin_dir_url( $file ) );
		$this->set( 'basename', plugin_basename( $file ) );

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		$data = get_plugin_data( $file, true, false );
		
		$this->set( 'name', $data['Name'] ?? '' );
		$this->set( 'version', $data['Version'] ?? '' );
		$this->headers = new Fluent( $data );

	}

}
