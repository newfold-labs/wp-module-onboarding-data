import { useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { ToggleField, Container } from '@newfold/ui-component-library';
import { NewfoldRuntime } from '@newfold/wp-module-runtime';

import { STORE_NAME } from '../../data/constants';
import TEXT from './getSkip404Text';

const {
	skip404Title,
	skip404Description,
	skip404OptionLabel,
	skip404NoticeTitle,
	optionNotSet,
} = TEXT;

const Skip404 = () => {
	const [ skip404, setSkip404 ] = useState(
		NewfoldRuntime?.sdk?.skip404?.is_active ?? false
	);
	const { pushNotification } = useDispatch( STORE_NAME );

	const handleSkip404Change = () => {
		const newValue = ! skip404;

		apiFetch( {
			path: '/newfold-performance/v1/skip404',
			method: 'POST',
			data: {
				field: {
					id: 'skip404',
					value: newValue,
				},
			},
		} )
			.then( () => {
				pushNotification( 'skip404-change-notice', {
					title: skip404NoticeTitle,
					variant: 'success',
					autoDismiss: 5000,
				} );
			} )
			.catch( () => {
				pushNotification( 'skip404-change-notice', {
					title: optionNotSet,
					variant: 'error',
					autoDismiss: 5000,
				} );
			} );

		setSkip404( newValue );
	};

	return (
		<Container.SettingsField
			title={ skip404Title }
			description={ skip404Description }
		>
			<ToggleField
				id="skip-404"
				label={ skip404OptionLabel }
				checked={ skip404 }
				onChange={ handleSkip404Change }
			/>
		</Container.SettingsField>
	);
};

export default Skip404;
