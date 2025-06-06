<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * Monitors Jetpack events
 */
class Jetpack extends Listener {

	/**
	 * Brand code constants
	 *
	 * @var brand_code
	 */
	private $brand_code = array(
		'bluehost'        => '86241',
		'hostgator'       => '57686',
		'web'             => '86239',
		'crazy-domains'   => '57687',
		'hostgator-india' => '57686',
		'bluehost-india'  => '86241',
		'hostgator-latam' => '57686',
		'default'         => '86240',
	);

	/**
	 * Register the hooks for the listener
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Connected
		add_action( 'jetpack_site_registered', array( $this, 'connected' ), 10, 3 );

		// Module enabled/disabled
		add_action( 'jetpack_pre_activate_module', array( $this, 'module_enabled' ) );
		add_action( 'jetpack_pre_deactivate_module', array( $this, 'module_disabled' ) );
		add_action( 'activated_plugin', array( $this, 'detect_plugin_activation' ), 10, 1 );
		// Publicize
		add_action( 'publicize_save_meta', array( $this, 'publicize' ), 10, 4 );
	}

	/**
	 * Jetpack connected
	 *
	 * @param integer        $id Jetpack Site ID
	 * @param string         $secret Jetpack blog token
	 * @param integer|boolan $is_public Whether the site is public
	 * @return void
	 */
	public function connected( $id, $secret, $is_public ) {
		$this->push(
			'jetpack_connected',
			array(
				'id'     => $id,
				'public' => $is_public,
			)
		);
	}

	/**
	 * Jetpack module enabled
	 *
	 * @param string $module Name of the module
	 * @return void
	 */
	public function module_enabled( $module ) {
		$this->push(
			'jetpack_module_enabled',
			array(
				'label_key' => 'module',
				'module'    => $module,
			)
		);
	}

	/**
	 * Jetpack module disabled
	 *
	 * @param string $module Name of the module
	 * @return void
	 */
	public function module_disabled( $module ) {
		$this->push(
			'jetpack_module_disabled',
			array(
				'label_key' => 'module',
				'module'    => $module,
			)
		);
	}

	/**
	 * Post publicized
	 *
	 * @param bool    $submit_post Whether to submit the post
	 * @param integer $post_id ID of the post being publicized
	 * @param string  $service_name Service name
	 * @param array   $connection Array of connection details
	 * @return void
	 */
	public function publicize( $submit_post, $post_id, $service_name, $connection ) {
		// Bail if it's not being publicized
		if ( ! $submit_post ) {
			return;
		}
		$this->push(
			'jetpack_publicized',
			array(
				'label_key' => 'service',
				'service'   => $service_name,
			)
		);
	}

	/**
	 * Post publicized
	 *
	 * @param bool $plugin Plugin information
	 * @return void
	 */
	public function detect_plugin_activation( $plugin ) {
		$container = container();
		if ( 'jetpack/jetpack.php' === $plugin ) {
			$brand = $container->plugin()->brand;
			if ( empty( $brand ) || ! array_key_exists( $brand, $this->brand_code ) ) {
					$brand = 'default';
			}
			$jetpack_affiliate_code = get_option( 'jetpack_affiliate_code' );
			! $jetpack_affiliate_code &&
											update_option( 'jetpack_affiliate_code', $this->brand_code[ $brand ] );
		}
	}
}
