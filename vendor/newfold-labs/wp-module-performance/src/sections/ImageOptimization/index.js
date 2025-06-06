import { useState, useEffect } from '@wordpress/element';
import { Alert, Container, ToggleField } from '@newfold/ui-component-library';
import apiFetch from '@wordpress/api-fetch';
import { useDispatch } from '@wordpress/data';
import { STORE_NAME } from '../../data/constants';
import getImageOptimizationText from './getImageOptimizationText';

const ImageOptimization = () => {
	const {
		imageOptimizationSettingsTitle,
		imageOptimizationSettingsDescription,
		imageOptimizationUsage,
		imageOptimizationProcessed,
		imageOptimizationPerMonth,
		imageOptimizationBannedMessage,
		imageOptimizationLoadingMessage,
		imageOptimizationErrorMessage,
		imageOptimizationNoSettings,
		imageOptimizationEnabledLabel,
		imageOptimizationEnabledDescription,
		imageOptimizationAutoOptimizeLabel,
		imageOptimizationAutoOptimizeDescription,
		imageOptimizationBulkOptimizeLabel,
		imageOptimizationBulkOptimizeDescription,
		imageOptimizationBulkOptimizeButtonLabel,
		imageOptimizationAutoDeleteLabel,
		imageOptimizationAutoDeleteDescription,
		imageOptimizationLazyLoadingLabel,
		imageOptimizationLazyLoadingDescription,
		imageOptimizationPreferWebPLabel,
		imageOptimizationPreferWebPDescription,
		imageOptimizationUpdatedTitle,
		imageOptimizationUpdatedDescription,
		imageOptimizationUpdateErrorTitle,
		imageOptimizationGenericErrorMessage,
	} = getImageOptimizationText();

	const [ settings, setSettings ] = useState( null );
	const [ isError, setIsError ] = useState( false );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isBanned, setIsBanned ] = useState( false );
	const [ monthlyUsage, setMonthlyUsage ] = useState( {
		monthlyRequestCount: 0,
		maxRequestsPerMonth: 100000,
	} );

	const { pushNotification } = useDispatch( STORE_NAME );
	const apiUrl = '/wp/v2/settings';

	const fetchSettings = async () => {
		setIsLoading( true );
		setIsError( false );

		try {
			const res = await apiFetch( { path: apiUrl } );
			const fetched = res?.nfd_image_optimization || {};
			setSettings( fetched );
			setIsBanned( fetched?.banned_status || false );
			setMonthlyUsage(
				fetched?.monthly_usage || {
					monthlyRequestCount: 0,
					maxRequestsPerMonth: 100000,
				}
			);
		} catch {
			setIsError( true );
		} finally {
			setIsLoading( false );
		}
	};

	const updateSettings = async ( newSettings ) => {
		setIsError( false );
		try {
			const res = await apiFetch( {
				path: apiUrl,
				method: 'POST',
				data: { nfd_image_optimization: newSettings },
			} );
			const updated = res?.nfd_image_optimization || {};
			setSettings( updated );
			setIsBanned( updated?.banned_status || false );
			setMonthlyUsage(
				updated?.monthly_usage || {
					monthlyRequestCount: 0,
					maxRequestsPerMonth: 100000,
				}
			);
			pushNotification( 'img-opt-updated', {
				title: imageOptimizationUpdatedTitle,
				description: imageOptimizationUpdatedDescription,
				variant: 'success',
				autoDismiss: 5000,
			} );
		} catch {
			setIsError( true );
			pushNotification( 'img-opt-error', {
				title: imageOptimizationUpdateErrorTitle,
				description: imageOptimizationGenericErrorMessage,
				variant: 'error',
				autoDismiss: 8000,
			} );
		}
	};

	const handleToggle = ( field, value ) => {
		if ( isBanned ) return;

		const updated = { ...settings };

		switch ( field ) {
			case 'enabled':
				updated.enabled = value;
				updated.auto_optimized_uploaded_images.enabled = value;
				updated.auto_optimized_uploaded_images.auto_delete_original_image =
					value;
				updated.bulk_optimization = value;
				updated.lazy_loading.enabled = value;
				updated.prefer_optimized_image_when_exists = value;
				break;
			case 'autoOptimizeEnabled':
				updated.auto_optimized_uploaded_images.enabled = value;
				break;
			case 'bulkOptimize':
				updated.bulk_optimization = value;
				break;
			case 'lazyLoading':
				updated.lazy_loading.enabled = value;
				break;
			case 'autoDeleteOriginalImage':
				updated.auto_optimized_uploaded_images.auto_delete_original_image =
					value;
				break;
			case 'preferOptimizedImageWhenExists':
				updated.prefer_optimized_image_when_exists = value;
				break;
			default:
				break;
		}

		if (
			field !== 'autoDeleteOriginalImage' &&
			! updated.bulk_optimization &&
			! updated.auto_optimized_uploaded_images.enabled
		) {
			updated.auto_optimized_uploaded_images.auto_delete_original_image = false;
		}

		setSettings( updated );
		updateSettings( updated );
	};

	useEffect( () => {
		fetchSettings();
	}, [] );

	if ( isLoading ) return <p>{ imageOptimizationLoadingMessage }</p>;

	if ( isError )
		return <Alert variant="error">{ imageOptimizationErrorMessage }</Alert>;

	if ( ! settings ) {
		return (
			<Container.SettingsField
				title={ imageOptimizationSettingsTitle }
				description={ imageOptimizationSettingsDescription }
			>
				<p>{ imageOptimizationNoSettings }</p>
			</Container.SettingsField>
		);
	}

	const {
		enabled,
		prefer_optimized_image_when_exists: preferOptimized = true,
		auto_optimized_uploaded_images: auto = {},
		lazy_loading: lazy = { enabled: true },
		bulk_optimization: bulk = false,
	} = settings;

	const { enabled: autoEnabled, auto_delete_original_image: autoDelete } =
		auto;

	const mediaLibraryLink = () => {
		const basePath = window.location.pathname.split( '/wp-admin' )[ 0 ];
		return `${ window.location.origin }${ basePath }/wp-admin/upload.php?autoSelectBulk=true`;
	};

	return (
		<Container.SettingsField
			title={ imageOptimizationSettingsTitle }
			description={
				<>
					{ imageOptimizationSettingsDescription }
					<br />
					<br />
					<p>
						<strong>{ imageOptimizationUsage }</strong>{ ' ' }
						{ monthlyUsage.monthlyRequestCount }{ ' ' }
						{ imageOptimizationProcessed }{ ' ' }
						{ monthlyUsage.maxRequestsPerMonth / 1000 }k
						{ imageOptimizationPerMonth }
					</p>
					{ isBanned && (
						<p className="nfd-text-red">
							{ imageOptimizationBannedMessage }
						</p>
					) }
				</>
			}
		>
			{ isBanned && <div className="nfd-overlay" /> }
			<div
				className={ `nfd-flex nfd-flex-col nfd-gap-6 ${
					isBanned ? 'nfd-disabled' : ''
				}` }
			>
				<ToggleField
					id="image-optimization-enabled"
					label={ imageOptimizationEnabledLabel }
					description={ imageOptimizationEnabledDescription }
					checked={ enabled }
					onChange={ () => handleToggle( 'enabled', ! enabled ) }
					disabled={ isBanned }
				/>

				{ enabled && (
					<>
						<ToggleField
							id="auto-optimize-images"
							label={ imageOptimizationAutoOptimizeLabel }
							description={
								imageOptimizationAutoOptimizeDescription
							}
							checked={ autoEnabled }
							onChange={ () =>
								handleToggle(
									'autoOptimizeEnabled',
									! autoEnabled
								)
							}
							disabled={ isBanned }
						/>

						<ToggleField
							id="bulk-optimize-images"
							label={ imageOptimizationBulkOptimizeLabel }
							description={
								<>
									<p>
										{
											imageOptimizationBulkOptimizeDescription
										}
									</p>
									{ bulk && (
										<a
											href={ mediaLibraryLink() }
											target="_blank"
											rel="noopener noreferrer"
										>
											{
												imageOptimizationBulkOptimizeButtonLabel
											}
										</a>
									) }
								</>
							}
							checked={ bulk }
							onChange={ () =>
								handleToggle( 'bulkOptimize', ! bulk )
							}
							disabled={ isBanned }
						/>

						<ToggleField
							id="prefer-webp-when-exists"
							label={ imageOptimizationPreferWebPLabel }
							description={
								imageOptimizationPreferWebPDescription
							}
							checked={ preferOptimized }
							onChange={ () =>
								handleToggle(
									'preferOptimizedImageWhenExists',
									! preferOptimized
								)
							}
							disabled={ isBanned }
						/>

						<ToggleField
							id="auto-delete-original"
							label={ imageOptimizationAutoDeleteLabel }
							description={
								imageOptimizationAutoDeleteDescription
							}
							checked={ autoDelete }
							onChange={ () =>
								handleToggle(
									'autoDeleteOriginalImage',
									! autoDelete
								)
							}
							disabled={ ( ! autoEnabled && ! bulk ) || isBanned }
						/>

						<ToggleField
							id="lazy-loading-enabled"
							label={ imageOptimizationLazyLoadingLabel }
							description={
								imageOptimizationLazyLoadingDescription
							}
							checked={ lazy.enabled }
							onChange={ () =>
								handleToggle( 'lazyLoading', ! lazy.enabled )
							}
							disabled={ isBanned }
						/>
					</>
				) }
			</div>
		</Container.SettingsField>
	);
};

export default ImageOptimization;
