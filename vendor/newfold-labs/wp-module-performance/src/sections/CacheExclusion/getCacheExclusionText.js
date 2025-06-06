import { __ } from '@wordpress/i18n';

const getCacheExclusionText = () => ( {
	cacheExclusionTitle: __( 'Exclude from cache', 'wp-module-performance' ),
	cacheExclusionDescription: __(
		'This setting controls what pages pass a “no-cache” header so that page caching and browser caching is not used.',
		'wp-module-performance'
	),
	cacheExclusionSaved: __( 'Cache Exclusion saved', 'wp-module-performance' ),
	cacheExclusionSaveButton: __( 'Save', 'wp-module-performance' ),
} );

export default getCacheExclusionText;
