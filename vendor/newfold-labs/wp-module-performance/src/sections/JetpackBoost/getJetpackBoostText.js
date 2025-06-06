import { __, sprintf } from '@wordpress/i18n';

const getJetpackBoostText = () => ( {
	performanceAdvancedSettingsTitle: __(
		'Advanced settings',
		'wp-module-performance'
	),
	performanceAdvancedSettingsDescription: __(
		'Additional speed and scalability features powered by Jetpack Boost to make your site as fast as it can be.',
		'wp-module-performance'
	),
	optionSet: __( 'Option saved correctly', 'wp-module-performance' ),
	optionNotSet: __( 'Error saving option', 'wp-module-performance' ),
	upgradeModule: __( 'Upgrade to unlock', 'wp-module-performance' ),
	jetpackBoostCtaText: __(
		'Install Jetpack Boost to unlock',
		'wp-module-performance'
	),
	jetpackBoostInstalling: __(
		'Installing Jetpack Boost…',
		'wp-module-performance'
	),
	jetpackBoostActivated: __(
		'Jetpack Boost is now active',
		'wp-module-performance'
	),
	jetpackBoostActivationFailed: __(
		'Activation failed',
		'wp-module-performance'
	),
	jetpackBoostCriticalCssTitle: __(
		'Optimize Critical CSS Loading (manual)',
		'wp-module-performance'
	),
	jetpackBoostCriticalCssDescription: __(
		'Move important styling information to the start of the page, which helps pages display your content sooner, so your users don’t have to wait for the entire page to load.',
		'wp-module-performance'
	),
	jetpackBoostCriticalCssButton: __(
		'Generate CSS',
		'wp-module-performance'
	),
	jetpackBoostCriticalCssGenerationSuccess: __(
		'Critical CSS generated successfully.',
		'wp-module-performance'
	),
	jetpackBoostCriticalCssGenerationText: __(
		'Keep this page opened until the process finish',
		'wp-module-performance'
	),
	jetpackBoostCriticalCssGenerationIssue: __(
		'Error generating Critical CSS, try again',
		'wp-module-performance'
	),

	jetpackBoostCriticalCssPremiumTitle: __(
		'Optimize Critical CSS Loading (UPGRADED)',
		'wp-module-performance'
	),
	jetpackBoostCriticalCssUpgradeTitle: __(
		'Generate Critical CSS Automatically',
		'wp-module-performance'
	),
	jetpackBoostCriticalCssPremiumDescription: sprintf(
		// translators: %1$s is a line break (<br>), %2$s is the opening <strong> tag, %3$s is the closing </strong> tag.
		__(
			'Move important styling information to the start of the page, which helps pages display your content sooner, so your users don’t have to wait for the entire page to load.%1$s %2$sBoost will automatically generate your Critical CSS%3$s whenever you make changes to the HTML or CSS structure of your site.',
			'wp-module-performance'
		),
		'<br><br>',
		'<strong>',
		'</strong>'
	),

	jetpackBoostRenderBlockingTitle: __(
		'Defer Non-Essential JavaScript',
		'wp-module-performance'
	),
	jetpackBoostRenderBlockingDescription: __(
		'Run non-essential JavaScript after the page has loaded so that styles and images can load more quickly.',
		'wp-module-performance'
	),

	jetpackBoostMinifyJsTitle: __( 'Concatenate JS', 'wp-module-performance' ),
	jetpackBoostMinifyJsDescription: __(
		'Scripts are grouped by their original placement, concatenated and minified to reduce site loading time and reduce the number of requests.',
		'wp-module-performance'
	),
	jetpackBoostExcludeJsTitle: __(
		'Exclude JS Strings',
		'wp-module-performance'
	),

	jetpackBoostMinifyCssTitle: __(
		'Concatenate CSS',
		'wp-module-performance'
	),
	jetpackBoostMinifyCssDescription: __(
		'Styles are grouped by their original placement, concatenated and minified to reduce site loading time and reduce the number of requests.',
		'wp-module-performance'
	),
	jetpackBoostExcludeCssTitle: __(
		'Exclude CSS Strings',
		'wp-module-performance'
	),
} );

export default getJetpackBoostText;
