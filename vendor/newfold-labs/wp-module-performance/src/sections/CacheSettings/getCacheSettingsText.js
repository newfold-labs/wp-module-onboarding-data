import { __ } from '@wordpress/i18n';

const getCacheSettingsText = () => {
	return {
		title: __( 'Cache Level', 'wp-module-performance' ),
		description: __(
			'Boost speed and performance by storing a copy of your website content, files, and images online so the pages of your website load faster for your visitors.',
			'wp-module-performance'
		),
		noticeTitle: __( 'Cache setting saved', 'wp-module-performance' ),

		options: [
			{
				label: __( 'Disabled', 'wp-module-performance' ),
				description: __(
					'No cache enabled. Every page load is fresh. Not recommended.',
					'wp-module-performance'
				),
				notice: __( 'Caching disabled.', 'wp-module-performance' ),
				value: 0,
			},
			{
				label: __( 'Assets Only', 'wp-module-performance' ),
				description: __(
					'Cache static assets like images and the appearance of your site for 1 hour. Tuned for online stores and member sites that need to be fresh.',
					'wp-module-performance'
				),
				notice: __(
					'Cache enabled for assets only.',
					'wp-module-performance'
				),
				value: 1,
			},
			{
				label: __( 'Assets & Web Pages', 'wp-module-performance' ),
				description: __(
					'Cache static assets for 24 hours and web pages for 2 hours. Tuned for sites that change at least weekly.',
					'wp-module-performance'
				),
				notice: __(
					'Cache enabled for assets and pages.',
					'wp-module-performance'
				),
				value: 2,
			},
			{
				label: __(
					'Assets & Web Pages - Extended',
					'wp-module-performance'
				),
				description: __(
					'Cache static assets for 1 week and web pages for 8 hours. Tuned for sites that update a few times a month or less.',
					'wp-module-performance'
				),
				notice: __(
					'Cache enabled for assets and pages (extended).',
					'wp-module-performance'
				),
				value: 3,
			},
		],
	};
};

export default getCacheSettingsText;
