document.addEventListener( 'DOMContentLoaded', () => {
	// Access the exclusions directly from window.nfdPerformance
	const performanceSettings = window.nfdPerformance || {};
	const imageOptimization = performanceSettings.imageOptimization || {};
	const lazyLoading = imageOptimization.lazyLoading || {};
	const excludedClasses = lazyLoading.classes || [];
	const excludedAttributes = lazyLoading.attributes || [];

	const shouldLazyLoad = ( element ) => {
		const hasExcludedClass = excludedClasses.some( ( cls ) =>
			element.classList.contains( cls )
		);
		const hasExcludedAttribute = excludedAttributes.some( ( attr ) =>
			element.hasAttribute( attr )
		);
		const alreadyLazy = element.getAttribute( 'loading' ) === 'lazy';

		return ! hasExcludedClass && ! hasExcludedAttribute && ! alreadyLazy;
	};

	const addLazyLoading = ( elements ) => {
		elements.forEach( ( el ) => {
			if ( shouldLazyLoad( el ) ) {
				el.setAttribute( 'loading', 'lazy' );
			}
		} );
	};

	// Observe dynamically added images
	const observer = new MutationObserver( ( mutations ) => {
		mutations.forEach( ( mutation ) => {
			Array.from( mutation.addedNodes ).forEach( ( node ) => {
				if ( node.nodeType === 1 && node.tagName === 'IMG' ) {
					// Process only new <img> elements
					addLazyLoading( [ node ] );
				} else if ( node.nodeType === 1 ) {
					// If a non-<img> element is added, check for nested <img> tags
					const nestedImages = node.querySelectorAll( 'img' );
					if ( nestedImages.length > 0 ) {
						addLazyLoading( nestedImages );
					}
				}
			} );
		} );
	} );

	observer.observe( document.body, { childList: true, subtree: true } );
} );
