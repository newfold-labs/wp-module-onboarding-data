import {
	Button,
	Container,
	TextareaField,
} from '@newfold/ui-component-library';

import { useState, useEffect, useRef } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

import { STORE_NAME } from '../../data/constants';
import { NewfoldRuntime } from '@newfold/wp-module-runtime';
import getCacheExclusionText from './getCacheExclusionText';

const {
	cacheExclusionTitle,
	cacheExclusionDescription,
	cacheExclusionSaved,
	cacheExclusionSaveButton,
} = getCacheExclusionText();

// Custom hook to mimic componentDidUpdate behavior
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

const CacheExclusion = () => {
	const [ isEdited, setIsEdited ] = useState( false );
	const [ isError, setIsError ] = useState( false );
	const [ isSaved, setIsSaved ] = useState( false );

	const runtimeExclusion = NewfoldRuntime?.sdk?.cache?.exclusion ?? '';
	const [ currentValue, setCurrentValue ] = useState( runtimeExclusion );
	const [ cacheExclusion, setCacheExclusion ] = useState( runtimeExclusion );

	const apiUrl = NewfoldRuntime.createApiUrl(
		'/newfold-performance/v1/cache/settings'
	);
	const { pushNotification } = useDispatch( STORE_NAME );

	const makeNotice = (
		id,
		title,
		description,
		variant = 'success',
		duration = false
	) => {
		pushNotification( id, {
			title,
			description,
			variant,
			autoDismiss: duration,
		} );
	};

	const handleCacheExclusionChange = ( e ) => {
		const newValue = e.target.value;
		setIsEdited( newValue !== cacheExclusion );
		setCurrentValue( newValue );
	};

	const handleSave = () => {
		apiFetch( {
			url: apiUrl,
			method: 'POST',
			data: { cacheExclusion: currentValue },
		} )
			.then( () => {
				setIsSaved( true );
				setCacheExclusion( currentValue );
				setIsEdited( false );
			} )
			.catch( ( error ) => {
				setIsError( error.message );
			} );
	};

	useUpdateEffect( () => {
		makeNotice(
			'cache-exclusion-notice',
			cacheExclusionTitle,
			! isError ? cacheExclusionSaved : isError,
			! isError ? 'success' : 'error',
			5000
		);
		setIsSaved( false );
	}, [ isSaved, isError ] );

	return (
		<Container.SettingsField
			title={ cacheExclusionTitle }
			description={ cacheExclusionDescription }
		>
			<TextareaField
				id="cache-exclusion"
				name="cache-exclusion"
				onChange={ handleCacheExclusionChange }
				value={ currentValue }
				rows="1"
				label={ cacheExclusionTitle }
			/>
			{ isEdited && (
				<Button
					variant="secondary"
					className="save-cache-exclusion-button"
					onClick={ handleSave }
				>
					{ cacheExclusionSaveButton }
				</Button>
			) }
		</Container.SettingsField>
	);
};

export default CacheExclusion;
