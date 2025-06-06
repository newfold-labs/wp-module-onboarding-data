import domReady from '@wordpress/dom-ready';
import apiFetch from '@wordpress/api-fetch';

import { eventsAPI } from '../constants';

domReady( () => {
	const domObserver = new window.MutationObserver( ( mutationList ) => {
		for ( const mutation of mutationList ) {
			if ( mutation.type === 'childList' ) {
				for ( const addedNode of mutation.addedNodes ) {
					if (
						typeof addedNode === 'object' &&
						typeof addedNode.querySelectorAll === 'function'
					) {
						addedNode
							.querySelectorAll( '[data-survey-action]' )
							.forEach( ( ele ) => {
								ele.addEventListener( 'click', function ( e ) {
									if (
										e.target.getAttribute(
											'data-survey-option'
										) !== null
									) {
										apiFetch( {
											url: eventsAPI,
											method: 'POST',
											data: {
												action: this.getAttribute(
													'data-survey-action'
												),
												category: this.getAttribute(
													'data-survey-category'
												),
												data: {
													...JSON.parse(
														this.getAttribute(
															'data-survey-data'
														)
													),
													value: e.target.getAttribute(
														'data-survey-option'
													),
												},
											},
										} );
									}
								} );
							} );
					}
				}
			}
		}
	} );

	domObserver.observe( document.body, { childList: true, subtree: true } );
} );
