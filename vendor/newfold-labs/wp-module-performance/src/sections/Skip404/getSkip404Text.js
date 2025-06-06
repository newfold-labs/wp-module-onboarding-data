import { __ } from '@wordpress/i18n';

const TEXT = {
	skip404Title: __( 'Skip 404', 'wp-plugin-bluehost' ),
	skip404Description: __(
		'When enabled, static resources like images and fonts will use a default server 404 page and not WordPress 404 pages. Pages and posts will continue using WordPress for 404 pages.',
		'wp-plugin-bluehost'
	),
	skip404OptionLabel: __(
		'Enable Skip 404 Handling For Static Files',
		'wp-plugin-bluehost'
	),
	skip404NoticeTitle: __( 'Skip 404 saved', 'wp-plugin-bluehost' ),
	optionNotSet: __( 'Error saving option', 'wp-plugin-bluehost' ),
};

export default TEXT;
