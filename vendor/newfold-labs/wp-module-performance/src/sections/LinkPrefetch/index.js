import {
	Toggle,
	TextField,
	SelectField,
	Container,
} from '@newfold/ui-component-library';

import { useState, useEffect, useRef } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { NewfoldRuntime } from '@newfold/wp-module-runtime';

import { STORE_NAME } from '../../data/constants';
import getLinkPrefetchText from './getLinkPrefetchText';

let ignoreKeywordsTimer = null;

const useUpdateEffect = ( effect, deps ) => {
	const isFirst = useRef( true );
	useEffect( () => {
		if ( isFirst.current ) {
			isFirst.current = false;
			return;
		}
		return effect();
	}, deps );
};

const LinkPrefetch = () => {
	const {
		linkPrefetchTitle,
		linkPrefetchDescription,
		linkPrefetchNoticeTitle,
		linkPrefetchActivateOnDesktopDescription,
		linkPrefetchActivateOnDesktopLabel,
		linkPrefetchBehaviorLabel,
		linkPrefetchBehaviorMouseDownLabel,
		linkPrefetchBehaviorMouseDownDescription,
		linkPrefetchBehaviorMouseHoverLabel,
		linkPrefetchBehaviorMouseHoverDescription,
		linkPrefetchActivateOnMobileDescription,
		linkPrefetchActivateOnMobileLabel,
		linkPrefetchBehaviorMobileTouchstartLabel,
		linkPrefetchBehaviorMobileTouchstartDescription,
		linkPrefetchBehaviorMobileViewportLabel,
		linkPrefetchBehaviorMobileViewportDescription,
		linkPrefetchIgnoreKeywordsLabel,
		linkPrefetchIgnoreKeywordsDescription,
	} = getLinkPrefetchText();

	const sdkSettings = NewfoldRuntime.sdk?.linkPrefetch?.settings || {};
	const [ settings, setSettings ] = useState( sdkSettings );
	const [ ignoreKeywords, setIgnoreKeywords ] = useState(
		sdkSettings.ignoreKeywords || ''
	);
	const [ isError, setIsError ] = useState( false );

	const { pushNotification } = useDispatch( STORE_NAME );

	const apiUrl = NewfoldRuntime.createApiUrl(
		'/newfold-performance/v1/link-prefetch/settings'
	);

	const handleChangeOption = ( option, value ) => {
		if ( ! ( option in settings ) ) return;

		const updatedSettings = { ...settings, [ option ]: value };

		apiFetch( {
			url: apiUrl,
			method: 'POST',
			data: { settings: updatedSettings },
		} )
			.then( () => {
				setSettings( updatedSettings );
			} )
			.catch( ( error ) => {
				setIsError( error.message );
			} );
	};

	const handleChangeOptionIgnoreKeywords = ( value ) => {
		clearTimeout( ignoreKeywordsTimer );
		value = value.substring( 0, 1000 );
		setIgnoreKeywords( value );
		ignoreKeywordsTimer = setTimeout( () => {
			handleChangeOption( 'ignoreKeywords', value );
		}, 700 );
	};

	useUpdateEffect( () => {
		pushNotification( 'link-prefetch-change-notice', {
			title: linkPrefetchTitle,
			description: isError || linkPrefetchNoticeTitle,
			variant: isError ? 'error' : 'success',
			autoDismiss: 5000,
		} );
	}, [ settings, isError ] );

	return (
		<Container.SettingsField
			title={ linkPrefetchTitle }
			description={ linkPrefetchDescription }
			data-cy="link-prefetch-settings"
		>
			{ /* Desktop Settings */ }
			<div className="nfd-toggle-field nfd-mb-6">
				<div>
					<label
						className="nfd-label"
						htmlFor="link-prefetch-active-desktop"
					>
						{ linkPrefetchActivateOnDesktopLabel }
					</label>
					<div className="nfd-select-field__description">
						{ linkPrefetchActivateOnDesktopDescription }
					</div>
				</div>
				<Toggle
					id="link-prefetch-active-desktop"
					data-cy="link-prefetch-active-desktop-toggle"
					screenReaderLabel={ linkPrefetchActivateOnDesktopLabel }
					checked={ settings.activeOnDesktop }
					onChange={ () =>
						handleChangeOption(
							'activeOnDesktop',
							! settings.activeOnDesktop
						)
					}
				/>
			</div>

			{ settings.activeOnDesktop && (
				<SelectField
					id="link-prefetch-behavior"
					data-cy="link-prefetch-behavior-desktop"
					label={ linkPrefetchBehaviorLabel }
					value={ settings.behavior }
					selectedLabel={
						settings.behavior === 'mouseDown'
							? linkPrefetchBehaviorMouseDownLabel
							: linkPrefetchBehaviorMouseHoverLabel
					}
					onChange={ ( v ) => handleChangeOption( 'behavior', v ) }
					description={
						settings.behavior === 'mouseDown'
							? linkPrefetchBehaviorMouseDownDescription
							: linkPrefetchBehaviorMouseHoverDescription
					}
					className="nfd-mb-6"
				>
					<SelectField.Option
						label={ linkPrefetchBehaviorMouseHoverLabel }
						value="mouseHover"
					/>
					<SelectField.Option
						label={ linkPrefetchBehaviorMouseDownLabel }
						value="mouseDown"
					/>
				</SelectField>
			) }

			{ /* Mobile Settings */ }
			<div className="nfd-toggle-field nfd-mb-6">
				<div>
					<label
						className="nfd-label"
						htmlFor="link-prefetch-active-mobile"
					>
						{ linkPrefetchActivateOnMobileLabel }
					</label>
					<div className="nfd-select-field__description">
						{ linkPrefetchActivateOnMobileDescription }
					</div>
				</div>
				<Toggle
					id="link-prefetch-active-mobile"
					data-cy="link-prefetch-active-mobile-toggle"
					screenReaderLabel={ linkPrefetchActivateOnMobileLabel }
					checked={ settings.activeOnMobile }
					onChange={ () =>
						handleChangeOption(
							'activeOnMobile',
							! settings.activeOnMobile
						)
					}
				/>
			</div>

			{ settings.activeOnMobile && (
				<SelectField
					id="link-prefetch-behavior-mobile"
					data-cy="link-prefetch-behavior-mobile"
					label={ linkPrefetchBehaviorLabel }
					value={ settings.mobileBehavior }
					selectedLabel={
						settings.mobileBehavior === 'touchstart'
							? linkPrefetchBehaviorMobileTouchstartLabel
							: linkPrefetchBehaviorMobileViewportLabel
					}
					onChange={ ( v ) =>
						handleChangeOption( 'mobileBehavior', v )
					}
					description={
						settings.mobileBehavior === 'touchstart'
							? linkPrefetchBehaviorMobileTouchstartDescription
							: linkPrefetchBehaviorMobileViewportDescription
					}
					className="nfd-mb-6"
				>
					<SelectField.Option
						label={ linkPrefetchBehaviorMobileTouchstartLabel }
						value="touchstart"
					/>
					<SelectField.Option
						label={ linkPrefetchBehaviorMobileViewportLabel }
						value="viewport"
					/>
				</SelectField>
			) }

			{ /* Ignore Keywords */ }
			{ ( settings.activeOnMobile || settings.activeOnDesktop ) && (
				<TextField
					id="link-prefetch-ignore-keywords"
					data-cy="link-prefetch-ignore-keywords"
					label={ linkPrefetchIgnoreKeywordsLabel }
					description={ linkPrefetchIgnoreKeywordsDescription }
					onChange={ ( e ) =>
						handleChangeOptionIgnoreKeywords( e.target.value )
					}
					value={ ignoreKeywords }
				/>
			) }
		</Container.SettingsField>
	);
};

export default LinkPrefetch;
