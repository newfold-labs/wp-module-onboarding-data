<?php

namespace NewfoldLabs\WP\Module\Performance\Images;

use DOMDocument;
use Exception;

/**
 * Manages the initialization and application of lazy loading for images.
 */
class ImageLazyLoader {

	/**
	 * Exclusion rules for lazy loading.
	 * Defines classes and attributes that should prevent lazy loading from being applied to specific images.
	 *
	 * @var array
	 */
	private static $exclusions = array(
		'classes'    => array(
			'nfd-performance-not-lazy',
			'a3-notlazy',
			'disable-lazyload',
			'no-lazy',
			'no-lazyload',
			'skip-lazy',
		),
		'attributes' => array(
			'data-lazy-src',
			'data-crazy-lazy="exclude"',
			'data-no-lazy',
			'data-no-lazy="1"',
		),
	);

	/**
	 * List of content filters where lazy loading will be applied.
	 * These filters modify various types of WordPress content.
	 *
	 * @var array
	 */
	private static $content_filters = array(
		'the_content',
		'post_thumbnail_html',
		'widget_text',
		'get_avatar',
	);

	/**
	 * Constructor to initialize the lazy loading feature.
	 */
	public function __construct() {
		// Enqueue the lazy loader script with inline settings.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_lazy_loader' ) );

		// Add filters to apply lazy loading to various content types.
		foreach ( self::$content_filters as $filter ) {
			add_filter( $filter, array( $this, 'apply_lazy_loading' ) );
		}

		// Hook into Gutenberg block rendering to apply lazy loading.
		add_filter( 'render_block', array( $this, 'apply_lazy_loading_to_blocks' ), 10, 2 );
	}

	/**
	 * Applies lazy loading to images within Gutenberg blocks.
	 *
	 * @param string $block_content The HTML content of the block.
	 * @param array  $block The block data array.
	 * @return string Modified block content with lazy loading applied.
	 */
	public function apply_lazy_loading_to_blocks( $block_content, $block ) {
		// Only target core/image blocks or other blocks with images.
		if ( 'core/image' === $block['blockName'] || strpos( $block_content, '<img' ) !== false ) {
			return $this->apply_lazy_loading( $block_content );
		}

		return $block_content;
	}

	/**
	 * Enqueues the lazy loader script file and adds inline exclusion settings.
	 */
	public function enqueue_lazy_loader() {
		$script_path = NFD_PERFORMANCE_BUILD_DIR . '/assets/image-lazy-loader.min.js';
		$script_url  = NFD_PERFORMANCE_BUILD_URL . '/assets/image-lazy-loader.min.js';

		// Register the script with version based on file modification time.
		wp_register_script(
			'nfd-performance-lazy-loader',
			$script_url,
			array(),
			file_exists( $script_path ) ? filemtime( $script_path ) : false,
			true
		);

		// Inject the exclusion settings into the script.
		wp_add_inline_script(
			'nfd-performance-lazy-loader',
			$this->get_inline_script(),
			'before'
		);

		wp_enqueue_script( 'nfd-performance-lazy-loader' );
	}

	/**
	 * Generates the inline script to define lazy loading exclusions.
	 * This script populates the `window.nfdPerformance` object with exclusion rules.
	 *
	 * @return string JavaScript code to inline.
	 */
	private function get_inline_script() {
		return 'window.nfdPerformance = window.nfdPerformance || {};
        window.nfdPerformance.imageOptimization = window.nfdPerformance.imageOptimization || {};
        window.nfdPerformance.imageOptimization.lazyLoading = ' . wp_json_encode( self::$exclusions ) . ';';
	}

	/**
	 * Applies lazy loading to images in HTML content.
	 * Skips images with specified exclusion classes or attributes.
	 *
	 * @param string $content The HTML content to process.
	 * @return string Modified HTML content with lazy loading applied, or original content on error.
	 */
	public function apply_lazy_loading( $content ) {
		// Return unmodified content if it is empty.
		if ( empty( $content ) ) {
			return $content;
		}

		$doc = new DOMDocument();
		// Suppress warnings from invalid or malformed HTML.
		libxml_use_internal_errors( true );

		try {
			// Attempt to parse the HTML content using htmlentities for encoding.
			$content = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $content . '</body></html>';
			if ( ! $doc->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ) ) {
				return $content;
			}
		} catch ( Exception $e ) {
			return $content;
		} finally {
			// Clear any errors collected during parsing to free memory.
			libxml_clear_errors();
		}

		$images = $doc->getElementsByTagName( 'img' );

		foreach ( $images as $image ) {
			$skip = false;

			// Check if the image has an excluded class.
			foreach ( self::$exclusions['classes'] as $class ) {
				if ( $image->hasAttribute( 'class' ) && strpos( $image->getAttribute( 'class' ), $class ) !== false ) {
					$skip = true;
					break;
				}
			}

			// Check if the image has an excluded attribute.
			foreach ( self::$exclusions['attributes'] as $attr ) {
				if ( $image->hasAttribute( $attr ) ) {
					$skip = true;
					break;
				}
			}

			if ( $skip ) {
				continue;
			}

			// Add the loading="lazy" attribute if not already present.
			if ( ! $image->hasAttribute( 'loading' ) ) {
				$image->setAttribute( 'loading', 'lazy' );
			}
		}

		// Extract the body content and return it.
		$body = $doc->getElementsByTagName( 'body' )->item( 0 );
		return $body ? $doc->saveHTML( $body ) : $content;
	}
}
