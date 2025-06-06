import apiFetch from '@wordpress/api-fetch';
import { useRef, useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import parse from 'html-react-parser';

import {
	Button,
	ToggleField,
	Textarea,
	FeatureUpsell,
	Spinner,
	ProgressBar,
	Container,
} from '@newfold/ui-component-library';

import { NewfoldRuntime } from '@newfold/wp-module-runtime';
import { STORE_NAME } from '../../data/constants';
import getJetpackBoostText from './getJetpackBoostText';
import InstallActivatePluginButton from './InstallActivatePluginButton';

const JetpackBoost = () => {
	const {
		performanceAdvancedSettingsTitle,
		performanceAdvancedSettingsDescription,
		optionSet,
		optionNotSet,
		upgradeModule,
		jetpackBoostCriticalCssTitle,
		jetpackBoostCriticalCssDescription,
		jetpackBoostCriticalCssButton,
		jetpackBoostCriticalCssGenerationSuccess,
		jetpackBoostCriticalCssGenerationText,
		jetpackBoostCriticalCssGenerationIssue,
		jetpackBoostCriticalCssPremiumTitle,
		jetpackBoostCriticalCssUpgradeTitle,
		jetpackBoostCriticalCssPremiumDescription,
		jetpackBoostRenderBlockingTitle,
		jetpackBoostRenderBlockingDescription,
		jetpackBoostMinifyJsTitle,
		jetpackBoostMinifyJsDescription,
		jetpackBoostExcludeJsTitle,
		jetpackBoostMinifyCssTitle,
		jetpackBoostMinifyCssDescription,
		jetpackBoostExcludeCssTitle,
	} = getJetpackBoostText();

	const currentUrl = window.location.href;
	const siteUrl = currentUrl.split( '/wp-admin/' )[ 0 ];
	const sdk = NewfoldRuntime.sdk?.jetpackboost || {};

	const isPremiumActive = sdk.jetpack_premium_is_active || false;
	const [ isModuleEnabled, setIsModuleEnabled ] = useState(
		sdk.is_active || false
	);
	const [ isLoading, setIsLoading ] = useState( false );
	const [ cssIsGenerating, setCssIsGenerating ] = useState( false );
	const [ progressBarValue, setProgressBarValue ] = useState( null );
	const debounceTimeout = useRef( null );

	const [ settings, setSettings ] = useState( {
		'critical-css': sdk.critical_css || false,
		'critical-css-premium': sdk.jcritical_css || false,
		'render-blocking-js': sdk.blocking_js || false,
		'minify-js': sdk.minify_js || false,
		'minify-js-excludes': sdk.minify_js_excludes || '',
		'minify-css': sdk.minify_css || false,
		'minify-css-excludes': sdk.minify_css_excludes || '',
	} );

	const { pushNotification } = useDispatch( STORE_NAME );

	const makeNotice = (
		id,
		title,
		description = '',
		variant = 'success',
		duration = 5000
	) => {
		pushNotification( id, {
			title,
			description,
			variant,
			autoDismiss: duration,
		} );
	};

	const handleOnChangeOption = ( value, id ) => {
		setIsLoading( true );
		if ( typeof value === 'object' ) {
			value = value.target.value;
		}

		const updatedSettings = { ...settings, [ id ]: value };
		setSettings( updatedSettings );

		if ( debounceTimeout.current ) {
			clearTimeout( debounceTimeout.current );
		}

		debounceTimeout.current = setTimeout( () => {
			apiFetch( {
				path: 'newfold-performance/v1/jetpack/settings',
				method: 'POST',
				data: { field: { id, value } },
			} )
				.then( () => {
					makeNotice( 'cache-level-change-notice', optionSet );
				} )
				.catch( () => {
					makeNotice(
						'cache-level-change-notice',
						optionNotSet,
						'',
						'error'
					);
				} )
				.finally( () => {
					setIsLoading( false );
				} );
		}, 500 );

		if ( id === 'critical-css' && value === true ) {
			handleRegenerateClick();
		}
	};

	const handleRegenerateClick = async () => {
		setCssIsGenerating( true );
		let iframe;
		try {
			await new Promise( ( resolve ) => setTimeout( resolve, 1000 ) );
			if ( ! sdk.jetpack_boost_connected ) {
				await apiFetch( {
					path: 'jetpack-boost/v1/connection',
					method: 'POST',
				} );
			}
			await apiFetch( {
				path: 'newfold-performance/v1/jetpack/regenerate_critical_css',
				method: 'POST',
			} );
			const adminUrl = `${ siteUrl }/wp-admin/admin.php?page=jetpack-boost`;
			iframe = document.createElement( 'iframe' );
			iframe.src = adminUrl;
			document.body.appendChild( iframe );
			iframe.style.height = '0';
			iframe.onload = function () {
				try {
					const iframeDocument =
						iframe.contentDocument || iframe.contentWindow.document;

					const progressBar = iframeDocument.querySelector(
						'div[role="progressbar"]'
					);
					if ( ! progressBar ) {
						iframe?.remove();
						setCssIsGenerating( false );

						makeNotice(
							'critical-css-generation-notice',
							jetpackBoostCriticalCssGenerationIssue,
							'',
							'error'
						);
						return;
					}

					setCssIsGenerating( false );
					let observer;
					const checkProgress = () => {
						const progressValue = parseInt(
							progressBar.getAttribute( 'aria-valuenow' ),
							10
						);
						setProgressBarValue( progressValue );

						if ( progressValue === 100 ) {
							setTimeout( () => {
								setProgressBarValue( null );
								iframe.remove();
								makeNotice(
									'cache-level-change-notice',
									jetpackBoostCriticalCssGenerationSuccess
								);
							}, 1000 );
							if ( typeof observer !== 'undefined' )
								observer.disconnect();
						}
					};
					/* global MutationObserver */
					if ( typeof MutationObserver !== 'undefined' ) {
						observer = new MutationObserver( checkProgress );
						observer.observe( progressBar, {
							attributes: true,
							attributeFilter: [ 'aria-valuenow' ],
						} );
						checkProgress();
					}
				} catch ( error ) {
					// eslint-disable-next-line no-console
					console.error( 'Error accessing iFrame:', error );
					makeNotice(
						'cache-level-change-notice',
						jetpackBoostCriticalCssGenerationIssue
					);
				}
			};
		} catch ( error ) {
			iframe?.remove();
			setCssIsGenerating( false );
			makeNotice(
				'critical-css-generation-notice',
				jetpackBoostCriticalCssGenerationIssue,
				'',
				'error'
			);
			// eslint-disable-next-line no-console
			console.error( error );
		}
	};

	const cssPremiumField = (
		<ToggleField
			id="critical-css-premium"
			label={
				isPremiumActive
					? jetpackBoostCriticalCssPremiumTitle
					: jetpackBoostCriticalCssUpgradeTitle
			}
			description={ parse( jetpackBoostCriticalCssPremiumDescription ) }
			checked={ !! settings[ 'critical-css-premium' ] }
			onChange={ ( value ) =>
				handleOnChangeOption( value, 'critical-css-premium' )
			}
		/>
	);

	return (
		<Container.SettingsField
			title={ performanceAdvancedSettingsTitle }
			description={ performanceAdvancedSettingsDescription }
		>
			<div
				className={ `nfd-performance-jetpack-boost-container-options ${
					isLoading ? 'is-loading' : ''
				} ${
					! isModuleEnabled
						? 'module-disabled nfd-inset-0 nfd-ring-1 nfd-ring-black nfd-ring-opacity-5 nfd-shadow-lg nfd-rounded-md'
						: ''
				}` }
			>
				{ ! isModuleEnabled && (
					<InstallActivatePluginButton
						setModuleStatus={ setIsModuleEnabled }
					/>
				) }

				{ isLoading && (
					<Spinner
						size="8"
						variant="primary"
						className="nfd-performance-jetpack-boost-loader nfd-text-primary"
					/>
				) }

				{ ! isPremiumActive && (
					<div className="section" style={ { marginBottom: '20px' } }>
						<ToggleField
							id="critical-css"
							label={ jetpackBoostCriticalCssTitle }
							description={ parse(
								jetpackBoostCriticalCssDescription
							) }
							checked={ !! settings[ 'critical-css' ] }
							onChange={ ( value ) =>
								handleOnChangeOption( value, 'critical-css' )
							}
						/>
						<div>
							{ settings[ 'critical-css' ] &&
								progressBarValue === null && (
									<Button
										size="small"
										variant="secondary"
										onClick={ handleRegenerateClick }
										disabled={
											progressBarValue > 0 ||
											cssIsGenerating
										}
										isLoading={ cssIsGenerating }
										className="nfd-performance-jetpack-boost-regenerate-critical-css"
									>
										{ jetpackBoostCriticalCssButton }
									</Button>
								) }

							{ progressBarValue !== null && (
								<>
									<ProgressBar
										max={ 100 }
										min={ 0 }
										progress={ progressBarValue }
									/>
									<p>
										{
											jetpackBoostCriticalCssGenerationText
										}
									</p>
								</>
							) }
						</div>
					</div>
				) }

				<div
					className="section automatic-critical-css"
					style={ { marginBottom: '20px' } }
				>
					{ isModuleEnabled && ! isPremiumActive ? (
						<FeatureUpsell
							cardText={ upgradeModule }
							cardLink={ `${ siteUrl }/wp-admin/admin.php?page=jetpack-boost#upgrade` }
						>
							{ cssPremiumField }
						</FeatureUpsell>
					) : (
						cssPremiumField
					) }
				</div>

				<div className="section" style={ { marginBottom: '20px' } }>
					<ToggleField
						id="render-blocking-js"
						label={ jetpackBoostRenderBlockingTitle }
						description={ parse(
							jetpackBoostRenderBlockingDescription
						) }
						checked={ !! settings[ 'render-blocking-js' ] }
						onChange={ ( value ) =>
							handleOnChangeOption( value, 'render-blocking-js' )
						}
					/>
				</div>

				<div className="section" style={ { marginBottom: '20px' } }>
					<ToggleField
						id="minify-js"
						label={ jetpackBoostMinifyJsTitle }
						description={ parse( jetpackBoostMinifyJsDescription ) }
						checked={ !! settings[ 'minify-js' ] }
						onChange={ ( value ) =>
							handleOnChangeOption( value, 'minify-js' )
						}
					/>
					{ settings[ 'minify-js' ] && (
						<div
							className="subsection"
							style={ { marginTop: '15px' } }
						>
							<p className="field-label">
								{ jetpackBoostExcludeJsTitle }
							</p>
							<Textarea
								id="minify-js-excludes"
								value={ settings[ 'minify-js-excludes' ] }
								onChange={ ( value ) =>
									handleOnChangeOption(
										value,
										'minify-js-excludes'
									)
								}
							/>
						</div>
					) }
				</div>

				<div className="section" style={ { marginBottom: '20px' } }>
					<ToggleField
						id="minify-css"
						label={ jetpackBoostMinifyCssTitle }
						description={ parse(
							jetpackBoostMinifyCssDescription
						) }
						checked={ !! settings[ 'minify-css' ] }
						onChange={ ( value ) =>
							handleOnChangeOption( value, 'minify-css' )
						}
					/>
					{ settings[ 'minify-css' ] && (
						<div
							className="subsection"
							style={ { marginTop: '15px' } }
						>
							<p className="field-label">
								{ jetpackBoostExcludeCssTitle }
							</p>
							<Textarea
								id="minify-css-excludes"
								value={ settings[ 'minify-css-excludes' ] }
								onChange={ ( value ) =>
									handleOnChangeOption(
										value,
										'minify-css-excludes'
									)
								}
							/>
						</div>
					) }
				</div>
			</div>
		</Container.SettingsField>
	);
};

export default JetpackBoost;
