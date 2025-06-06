import { __ } from '@wordpress/i18n';

const getLinkPrefetchText = () => ( {
	linkPrefetchTitle: __( 'Link Prefetch', 'wp-module-performance' ),
	linkPrefetchDescription: __(
		'Asks the browser to download and cache links on the page ahead of them being clicked on, so that when they are clicked they load almost instantly.',
		'wp-module-performance'
	),
	linkPrefetchNoticeTitle: __(
		'Link prefetching setting saved',
		'wp-module-performance'
	),
	linkPrefetchActivateOnDesktopDescription: __(
		'Enable link prefetching on desktop',
		'wp-module-performance'
	),
	linkPrefetchActivateOnDesktopLabel: __(
		'Activate on desktop',
		'wp-module-performance'
	),
	linkPrefetchBehaviorLabel: __( 'Behavior', 'wp-module-performance' ),
	linkPrefetchBehaviorMouseDownLabel: __(
		'Prefetch on Mouse Down',
		'wp-module-performance'
	),
	linkPrefetchBehaviorMouseDownDescription: __(
		'Prefetch on Mouse Down: Starts loading the page as soon as you click down, for faster response when you release the click.',
		'wp-module-performance'
	),
	linkPrefetchBehaviorMouseHoverLabel: __(
		'Prefetch on Mouse Hover (Recommended)',
		'wp-module-performance'
	),
	linkPrefetchBehaviorMouseHoverDescription: __(
		'Prefetch on Mouse Hover: Begins loading the page the moment your cursor hovers over a link',
		'wp-module-performance'
	),
	linkPrefetchActivateOnMobileDescription: __(
		'Enable link prefetching on mobile',
		'wp-module-performance'
	),
	linkPrefetchActivateOnMobileLabel: __(
		'Activate on mobile',
		'wp-module-performance'
	),
	linkPrefetchBehaviorMobileTouchstartLabel: __(
		'Prefetch on Touchstart (Recommended)',
		'wp-module-performance'
	),
	linkPrefetchBehaviorMobileTouchstartDescription: __(
		'Prefetch on Touchstart: Instantly starts loading the page as soon as you tap the screen, ensuring a quicker response when you lift your finger.',
		'wp-module-performance'
	),
	linkPrefetchBehaviorMobileViewportLabel: __(
		'Prefetch Above the Fold',
		'wp-module-performance'
	),
	linkPrefetchBehaviorMobileViewportDescription: __(
		"Prefetch Above the Fold: Loads links in your current view instantly, ensuring they're ready when you need them.",
		'wp-module-performance'
	),
	linkPrefetchIgnoreKeywordsLabel: __(
		'Exclude keywords',
		'wp-module-performance'
	),
	linkPrefetchIgnoreKeywordsDescription: __(
		'Exclude Keywords: A comma separated list of words or strings that will exclude a link from being prefetched. For example, excluding "app" will prevent https://example.com/apple from being prefetched.',
		'wp-module-performance'
	),
} );

export default getLinkPrefetchText;
