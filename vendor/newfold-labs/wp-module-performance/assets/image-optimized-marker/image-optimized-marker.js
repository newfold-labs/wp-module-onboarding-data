import './image-optimized-marker.css';

import { __ } from '@wordpress/i18n';

document.addEventListener( 'DOMContentLoaded', () => {
	/**
	 * Adds a marker to optimized images in the media library
	 */
	const markOptimizedImages = () => {
		document.querySelectorAll( '.attachment' ).forEach( ( attachment ) => {
			// Get media library data
			const mediaId = attachment.getAttribute( 'data-id' );
			const mediaData = wp.media.attachment( mediaId ).attributes;

			// Check if the image is optimized
			if (
				mediaData.nfdPerformanceImageOptimized &&
				! attachment.querySelector(
					'.nfd-performance-image-optimized-marker'
				)
			) {
				const marker = document.createElement( 'span' );
				marker.textContent = __( 'Optimized', 'wp-module-performance' );
				marker.className = 'nfd-performance-image-optimized-marker';

				attachment.querySelector( '.thumbnail' )?.appendChild( marker );
			}
		} );
	};

	// Monitor for changes in the media library
	const observer = new MutationObserver( markOptimizedImages );
	observer.observe( document.body, { childList: true, subtree: true } );

	// Initial run
	markOptimizedImages();
} );
