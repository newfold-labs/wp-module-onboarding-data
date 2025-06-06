import { __ } from '@wordpress/i18n';

const getClearCacheText = () => ( {
	clearCacheTitle: __( 'Clear Cache', 'wp-plugin-bluehost' ),
	clearCacheDescription: __(
		'We automatically clear your cache as you work (creating content, changing settings, installing plugins and more). But you can manually clear it here to be confident it is fresh.',
		'wp-plugin-bluehost'
	),
	clearCacheButton: __( 'Clear All Cache Now', 'wp-plugin-bluehost' ),
	clearCacheNoticeTitle: __( 'Cache cleared', 'wp-plugin-bluehost' ),
} );

export default getClearCacheText;
