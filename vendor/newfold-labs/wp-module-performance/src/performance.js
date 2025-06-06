import './styles/styles.css';

import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';

import { NewfoldRuntime } from '@newfold/wp-module-runtime';

import App from './components/App';

import './store';
import { NFD_PERFORMANCE_ELEMENT_ID } from './data/constants';

domReady( () => {
	const mountNode = document.getElementById( NFD_PERFORMANCE_ELEMENT_ID );

	const brand = NewfoldRuntime.sdk?.plugin?.brand;
	if ( brand ) {
		document.body.classList.add( `nfd-brand--${ brand }` );
	}

	if ( mountNode ) {
		const root = createRoot( mountNode );
		root.render( <App /> );
	}
} );
