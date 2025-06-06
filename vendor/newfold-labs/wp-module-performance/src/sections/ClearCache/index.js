import { useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { Button, Container } from '@newfold/ui-component-library';

import { STORE_NAME } from '../../data/constants';
import { NewfoldRuntime } from '@newfold/wp-module-runtime';
import getClearCacheText from './getClearCacheText';

const ClearCache = () => {
	const {
		clearCacheTitle,
		clearCacheDescription,
		clearCacheButton,
		clearCacheNoticeTitle,
	} = getClearCacheText();

	const apiUrl = NewfoldRuntime.createApiUrl(
		'/newfold-performance/v1/cache/settings'
	);
	const [ cacheLevel ] = useState( NewfoldRuntime?.sdk?.cache?.level ?? 0 );
	const { pushNotification } = useDispatch( STORE_NAME );

	const clearCache = () => {
		apiFetch( {
			url: apiUrl,
			method: 'DELETE',
		} )
			.then( () => {
				pushNotification( 'clear-cache-success', {
					title: clearCacheNoticeTitle,
					variant: 'success',
					autoDismiss: 5000,
				} );
			} )
			.catch( ( err ) => {
				pushNotification( 'clear-cache-error', {
					title: 'Failed to clear cache',
					description: err.message || 'Something went wrong.',
					variant: 'error',
					autoDismiss: 5000,
				} );
			} );
	};

	return (
		<Container.SettingsField
			title={ clearCacheTitle }
			description={ clearCacheDescription }
		>
			<Button
				variant="secondary"
				className="clear-cache-button"
				onClick={ clearCache }
				disabled={ cacheLevel <= 0 }
			>
				{ clearCacheButton }
			</Button>
		</Container.SettingsField>
	);
};

export default ClearCache;
