<?php

namespace NewfoldLabs\WP\Module\Performance\Skip404;

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\Performance\OptionListener;

use function WP_Forge\WP_Htaccess_Manager\addContent;
use function WP_Forge\WP_Htaccess_Manager\removeMarkers;

/**
 * Handles Skip 404 functionality.
 */
class Skip404 {

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Option name for skip 404 setting.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'newfold_skip_404_handling';

	/**
	 * The file marker name.
	 */
	const MARKER = 'Newfold Skip 404 Handling for Static Files';


	/**
	 * Constructor.
	 *
	 * @param Container $container The dependency injection container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;

		new OptionListener( self::OPTION_NAME, array( __CLASS__, 'maybe_add_rules' ) );

		add_filter( 'newfold_update_htaccess', array( $this, 'on_update_htaccess' ) );

		add_action( 'newfold_container_set', array( $this, 'handle_actions_newfold_container_set' ) );
		add_action( 'plugins_loaded', array( $this, 'handle_actions_on_plugins_loaded' ) );

		add_filter( 'newfold-runtime', array( $this, 'add_to_runtime' ), 100 );

		register_activation_hook( $container->plugin()->file, array( $this, 'on_activation' ) );
		register_deactivation_hook( $container->plugin()->file, array( $this, 'on_deactivation' ) );
	}


	/**
	 * Perform actions on enabling/disabling Performance feature
	 *
	 * @return void
	 */
	public function handle_actions_on_plugins_loaded() {
		add_action( 'newfold/features/action/onEnable:performance', array( $this, 'on_activation' ) );
		add_action( 'newfold/features/action/onDisable:performance', array( $this, 'on_deactivation' ) );
	}

	/**
	 * Detect if the feature needs to be performed or not
	 *
	 * @param Container $container Dependency injection container.
	 *
	 * @return bool
	 */
	public static function is_active( Container $container ) {
		return (bool) $container->has( 'isApache' ) && $container->get( 'isApache' );
	}

	/**
	 * Get value for SKIP404 option
	 *
	 * @return bool
	 */
	public static function get_value() {
		return (bool) get_option( self::OPTION_NAME, true );
	}

	/**
	 * When updating .htaccess, also update our rules as appropriate.
	 */
	public function on_update_htaccess() {
		self::maybe_add_rules( self::get_value() );

		// Remove the old option from EPC, if it exists
		if ( $this->container->get( 'hasMustUsePlugin' ) && absint( get_option( 'epc_skip_404_handling', 0 ) ) ) {
			update_option( 'epc_skip_404_handling', 0 );
			delete_option( 'epc_skip_404_handling' );
		}
	}

	/**
	 * Conditionally add or remove .htaccess rules based on option value.
	 *
	 * @param bool|null $shouldSkip404Handling if should skip 404 handling.
	 */
	public static function maybe_add_rules( $shouldSkip404Handling ) {
		(bool) $shouldSkip404Handling ? self::add_rules() : self::remove_rules();
	}

	/**
	 * Add our rules to the .htacces file.
	 */
	public static function add_rules() {
		$content = <<<HTACCESS
            <IfModule mod_rewrite.c>
                RewriteEngine On
                RewriteCond %{REQUEST_FILENAME} !-f
                RewriteCond %{REQUEST_FILENAME} !-d
                RewriteCond %{REQUEST_URI} !(robots\.txt|ads\.txt|[a-z0-9_\-]*sitemap[a-z0-9_\.\-]*\.(xml|xsl|html)(\.gz)?)
                RewriteCond %{REQUEST_URI} \.(css|htc|less|js|js2|js3|js4|html|htm|rtf|rtx|txt|xsd|xsl|xml|asf|asx|wax|wmv|wmx|avi|avif|avifs|bmp|class|divx|doc|docx|eot|exe|gif|gz|gzip|ico|jpg|jpeg|jpe|webp|json|mdb|mid|midi|mov|qt|mp3|m4a|mp4|m4v|mpeg|mpg|mpe|webm|mpp|otf|_otf|odb|odc|odf|odg|odp|ods|odt|ogg|ogv|pdf|png|pot|pps|ppt|pptx|ra|ram|svg|svgz|swf|tar|tif|tiff|ttf|ttc|_ttf|wav|wma|wri|woff|woff2|xla|xls|xlsx|xlt|xlw|zip)$ [NC]
                RewriteRule .* - [L]
            </IfModule>
            HTACCESS; // phpcs:ignore Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsedHeredocCloser

		addContent( self::MARKER, $content );
	}

	/**
	 * Remove our rules from the .htaccess file.
	 */
	public static function remove_rules() {
		removeMarkers( self::MARKER );
	}

	/**
	 * Handle activation logic.
	 */
	public static function on_activation() {
		self::maybe_add_rules( self::get_value() );
	}

	/**
	 * Handle deactivation logic.
	 */
	public static function on_deactivation() {
		self::remove_rules();
	}

	/**
	 * Add to Newfold SDK runtime.
	 *
	 * @param array $sdk SDK data.
	 * @return array SDK data.
	 */
	public function add_to_runtime( $sdk ) {
		$values = array(
			'is_active' => $this->get_value(),
		);

		return array_merge( $sdk, array( 'skip404' => $values ) );
	}
}
