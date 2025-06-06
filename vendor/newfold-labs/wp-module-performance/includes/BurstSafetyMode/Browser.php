<?php
namespace NewfoldLabs\WP\Module\Performance\BurstSafetyMode;

use WP_Forge\WP_Htaccess_Manager\htaccess;

/**
 * Browser cache.
 */
class Browser {
		/**
	 * The file marker name.
	 *
	 * @var string
	 */
	const MARKER = 'Newfold Browser Cache';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$responseHeaderManager = new ResponseHeaderManager();
		$responseHeaderManager->add_header( 'X-Newfold-Cache-Level', BURST_SAFETY_CACHE_LEVEL );
		$this->addRules();
	}

	/**
	 * Add htaccess rules.
	 */
	public static function addRules() {

		$file_typ_expirations = array(
			'default'         => '1 week',
			'text/html'       => '8 hours',
			'image/jpg'       => '1 week',
			'image/jpeg'      => '1 week',
			'image/gif'       => '1 week',
			'image/png'       => '1 week',
			'text/css'        => '1 week',
			'text/javascript' => '1 week',
			'application/pdf' => '1 month',
			'image/x-icon'    => '1 year',
		);

		$tab = "\t";

		$rules[] = '<IfModule mod_expires.c>';
		$rules[] = "{$tab}ExpiresActive On";

		foreach ( $file_typ_expirations as $file_type => $expiration ) {
			if ( 'default' === $file_type ) {
				$rules[] = "{$tab}ExpiresDefault \"access plus {$expiration}\"";
			} else {
				$rules[] = "{$tab}ExpiresByType {$file_type} \"access plus {$expiration}\"";
			}
		}

		$rules [] = '</IfModule>';

		$htaccess = new htaccess( self::MARKER );

		return $htaccess->addContent( $rules );
	}
}
