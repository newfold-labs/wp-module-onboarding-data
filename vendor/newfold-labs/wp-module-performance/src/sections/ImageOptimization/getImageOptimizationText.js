import { __ } from '@wordpress/i18n';

const getImageOptimizationText = () => ( {
	imageOptimizationSettingsTitle: __(
		'Image Optimization',
		'wp-module-performance'
	),
	imageOptimizationSettingsDescription: __(
		'We automatically optimize your uploaded images to WebP format for faster performance and reduced file sizes. You can also choose to delete the original images to save storage space.',
		'wp-module-performance'
	),
	imageOptimizationUsage: __( 'Usage:', 'wp-module-performance' ),
	imageOptimizationProcessed: __(
		'images processed of',
		'wp-module-performance'
	),
	imageOptimizationPerMonth: __( '/month', 'wp-module-performance' ),
	imageOptimizationBannedMessage: __(
		'This site no longer qualifies for image optimization as it has reached its usage limits.',
		'wp-module-performance'
	),
	imageOptimizationLoadingMessage: __(
		'Loading settings…',
		'wp-module-performance'
	),
	imageOptimizationErrorMessage: __(
		'Oops! Something went wrong. Please try again.',
		'wp-module-performance'
	),
	imageOptimizationNoSettings: __(
		'No settings available.',
		'wp-module-performance'
	),

	imageOptimizationEnabledLabel: __(
		'Enable Image Optimization',
		'wp-module-performance'
	),
	imageOptimizationEnabledDescription: __(
		'Enable or disable image optimization globally.',
		'wp-module-performance'
	),

	imageOptimizationAutoOptimizeLabel: __(
		'Automatically Optimize Uploaded Images',
		'wp-module-performance'
	),
	imageOptimizationAutoOptimizeDescription: __(
		'When enabled, all your new image uploads will be automatically optimized to WebP format, ensuring faster page loading and reduced file sizes.',
		'wp-module-performance'
	),

	imageOptimizationBulkOptimizeLabel: __(
		'Enable Bulk Optimization of Images',
		'wp-module-performance'
	),
	imageOptimizationBulkOptimizeDescription: __(
		'When enabled, allows bulk optimization of images in the media library.',
		'wp-module-performance'
	),
	imageOptimizationBulkOptimizeButtonLabel: __(
		'Go to Media Library',
		'wp-module-performance'
	),

	imageOptimizationAutoDeleteLabel: __(
		'Auto Delete Original Image',
		'wp-module-performance'
	),
	imageOptimizationAutoDeleteDescription: __(
		'When enabled, the original uploaded image is deleted and replaced with the optimized version, helping to save storage space. If disabled, the optimized image is saved as a separate file, retaining the original.',
		'wp-module-performance'
	),

	imageOptimizationLazyLoadingLabel: __(
		'Enable Lazy Loading',
		'wp-module-performance'
	),
	imageOptimizationLazyLoadingDescription: __(
		'Lazy loading defers the loading of images until they are visible on the screen, improving page load speed and performance.',
		'wp-module-performance'
	),

	imageOptimizationPreferWebPLabel: __(
		'Prefer Optimized Image When Exists',
		'wp-module-performance'
	),
	imageOptimizationPreferWebPDescription: __(
		'When enabled, optimized images will be served in place of original images when they exist, improving performance.',
		'wp-module-performance'
	),

	imageOptimizationUpdatedTitle: __(
		'Settings updated successfully',
		'wp-module-performance'
	),
	imageOptimizationUpdatedDescription: __(
		'Your image optimization settings have been saved.',
		'wp-module-performance'
	),

	imageOptimizationUpdateErrorTitle: __(
		'Error Updating Settings',
		'wp-module-performance'
	),
	imageOptimizationGenericErrorMessage: __(
		'Something went wrong while updating the settings. Please try again.',
		'wp-module-performance'
	),
} );

export default getImageOptimizationText;
