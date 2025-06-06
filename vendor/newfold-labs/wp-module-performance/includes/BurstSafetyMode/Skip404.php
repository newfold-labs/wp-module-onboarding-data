<?php
namespace NewfoldLabs\WP\Module\Performance\BurstSafetyMode;

use function WP_Forge\WP_Htaccess_Manager\addContent;
use function WP_Forge\WP_Htaccess_Manager\removeMarkers;

/**
 * Skip 404 cache type.
 */
class Skip404 {
	/**
	 * The file marker name.
	 */
	const MARKER = 'Newfold Skip 404 Handling for Static Files';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->addRules();
	}


	/**
	 * Add our rules to the .htacces file.
	 */
	public static function addRules() {
		$content = <<<HTACCESS
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_URI} !(robots\.txt|ads\.txt|[a-z0-9_\-]*sitemap[a-z0-9_\.\-]*\.(xml|xsl|html)(\.gz)?)
	RewriteCond %{REQUEST_URI} \.(css|htc|less|js|js2|js3|js4|html|htm|rtf|rtx|txt|xsd|xsl|xml|asf|asx|wax|wmv|wmx|avi|avif|avifs|bmp|class|divx|doc|docx|eot|exe|gif|gz|gzip|ico|jpg|jpeg|jpe|webp|json|mdb|mid|midi|mov|qt|mp3|m4a|mp4|m4v|mpeg|mpg|mpe|webm|mpp|otf|_otf|odb|odc|odf|odg|odp|ods|odt|ogg|ogv|pdf|png|pot|pps|ppt|pptx|ra|ram|svg|svgz|swf|tar|tif|tiff|ttf|ttc|_ttf|wav|wma|wri|woff|woff2|xla|xls|xlsx|xlt|xlw|zip)$ [NC]
	RewriteRule .* - [L]
</IfModule>
HTACCESS;

		addContent( self::MARKER, $content );
	}

	/**
	 * Remove our rules from the .htaccess file.
	 */
	public static function removeRules() {
		removeMarkers( self::MARKER );
	}
}
