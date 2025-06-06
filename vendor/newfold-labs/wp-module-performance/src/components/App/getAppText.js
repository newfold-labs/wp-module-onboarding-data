import { __ } from '@wordpress/i18n';

const getAppText = () => ( {
	title: __( 'Performance', 'wp-module-performance' ),
	description: __(
		'This is where you can manage performance settings for your website.',
		'wp-module-performance'
	),
} );

export default getAppText;
