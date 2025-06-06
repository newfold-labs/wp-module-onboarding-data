<?php

namespace WP_Forge\WP_Htaccess_Manager;

class htaccess {

	/**
	 * The file name.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * The marker name.
	 *
	 * @var string
	 */
	protected $marker;

	/**
	 * Constructor.
	 *
	 * @param string $file   The file path.
	 * @param string $marker The marker name.
	 */
	public function __construct( string $marker, string $file = null ) {
		$this->marker = $marker;
		$this->file   = $file ?? getDefaultHtaccessFile();
	}

	/**
	 * Add content with file markers to a file.
	 *
	 * @param string|array $content  The content to add to the file. Can be a string or an array of strings representing lines.
	 * @param string       $location The location to add to the file (e.g. before or after).
	 *
	 * @return bool
	 */
	public function addContent( $content, string $location = 'before' ) {
		return addContent( $this->marker, $content, $location, $this->file );
	}

	/**
	 * Read existing content from between the markers.
	 *
	 * @return string
	 */
	public function readContent() {
		return extractContent( $this->marker, $this->file );
	}

	/**
	 * Remove markers and the content between them.
	 */
	public function removeContent() {
		removeMarkers( $this->marker, $this->file );
	}

}
