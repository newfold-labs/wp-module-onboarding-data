<?php

namespace NewfoldLabs\WP\Module\AI\Utils;

use DOMDocument;

/**
 * Helper class to get generated patterns and replace them
 */
class PatternParser {

	/**
	 * The input pattern
	 *
	 * @var string
	 */
	public $pattern;

	/**
	 * The escaped pattern, to preserve block grammar
	 *
	 * @var string
	 */
	public $pattern_escaped;

	/**
	 * The pattern content
	 *
	 * @var array
	 */
	public $pattern_content = array();

	/**
	 * DOM Document for the parsing
	 *
	 * @var mixed
	 */
	private $document;

	/**
	 * The list of headings we are going to parse from the content
	 *
	 * @var array
	 */
	private $headings = array(
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
	);

	/**
	 * The tag names to be parsed
	 *
	 * @var array
	 */
	private $tag_names_to_parse = array( 'p', 'a' );

	/**
	 * The pattern representation
	 *
	 * @var array
	 */
	public $pattern_representation;

	/**
	 * The pattern representation with replaced content
	 *
	 * @var array
	 */
	public $replaced_pattern_representation;

	/**
	 * Constructor to initialize values
	 *
	 * @param string $pattern The initial pattern
	 */
	public function __construct( $pattern ) {
		$this->pattern_escaped = $pattern;
		$this->pattern         = trim( $pattern );
		$this->document        = new DOMDocument();
		$this->document->loadHTML( $this->pattern );
	}

	/**
	 * Function to extract the headings out of a pattern
	 */
	public function get_headings() {
		$headings = array();
		// Iterate and get all h1 through h6
		foreach ( $this->headings as $heading ) {
			$elements      = $this->document->getElementsByTagName( $heading );
			$heading_texts = array();
			foreach ( $elements as $element ) {
				array_push( $heading_texts, $element->textContent );
			}

			if ( ! empty( $heading_texts ) ) {
				$headings[ $heading ] = $heading_texts;
			}
		}

		return $headings;
	}

	/**
	 * Function to parse the pre defined tags from the content
	 *
	 * @param string $tag_name The tag name to be parsed
	 */
	public function get_tag_text( $tag_name ) {
		$paras         = array();
		$para_elements = $this->document->getElementsByTagName( $tag_name );
		if ( ! empty( $para_elements ) ) {
			foreach ( $para_elements as $element ) {
				array_push( $paras, $element->textContent );
			}
		}

		return $paras;
	}

	/**
	 * Get the pattern representation
	 */
	public function get_pattern_representation() {
		$representation = $this->get_headings();

		foreach ( $this->tag_names_to_parse as $tag_name ) {
			$tag_name_content = $this->get_tag_text( $tag_name );
			if ( ! empty( $tag_name_content ) ) {
				$representation[ $tag_name ] = $tag_name_content;
			}
		}

		$this->pattern_representation = $representation;
		return $representation;
	}

	/**
	 * Function to generate the replaced pattern representation
	 *
	 * @param array $generated_content The generated content as replacement for the existing one.
	 */
	public function replace_content( $generated_content ) {
		$this->replaced_pattern_representation = array();
		// Match with the generated representation
		foreach ( $this->pattern_representation as $tagname => $content ) {
			if ( array_key_exists( $tagname, $generated_content ) ) {
				$this->replaced_pattern_representation[ $tagname ] = array();
				$existing_content_length                           = count( $this->pattern_representation[ $tagname ] );
				for ( $i = 0; $i < $existing_content_length; $i++ ) {
					if ( array_key_exists( $i, $generated_content[ $tagname ] ) ) {
						array_push( $this->replaced_pattern_representation[ $tagname ], $generated_content[ $tagname ][ $i ] );
					} else {
						array_push(
							$this->replaced_pattern_representation[ $tagname ],
							$content[ $i ]
						);
					}
				}
			} else {
				$this->replaced_pattern_representation[ $tagname ] = $content;
			}
		}
	}

	/**
	 * Function to replace the generated content in initial pattern and return
	 *
	 * @param array $generated_content the content generated for this pattern.
	 *
	 * @returns The replaced pattern string
	 */
	public function get_replaced_pattern( $generated_content ) {
		$replaced_pattern = $this->pattern_escaped;
		$this->replace_content( $generated_content );
		foreach ( $this->replaced_pattern_representation as $replacement_tag => $replacement_contents ) {
			$content_length = count( $replacement_contents );
			// Do a string match and replace
			for ( $i = 0; $i < $content_length; $i++ ) {
				$replaced_pattern = str_replace(
					$this->pattern_representation[ $replacement_tag ][ $i ],
					$replacement_contents[ $i ],
					$replaced_pattern
				);
			}
		}
		return $replaced_pattern;
	}
}
