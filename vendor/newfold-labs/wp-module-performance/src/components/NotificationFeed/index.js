import { useSelect, useDispatch } from '@wordpress/data';
import { Notifications } from '@newfold/ui-component-library';
import { STORE_NAME } from '../../data/constants';

const NotificationFeed = () => {
	const feed = useSelect(
		( select ) => select( STORE_NAME ).getVisibleNotifications(),
		[]
	);

	const { dismissNotification } = useDispatch( STORE_NAME );

	return (
		<Notifications className="nfd-notifications--bottom-right">
			{ feed.map( ( [ key, { description, ...entry } ] ) => {
				const contentProps = Array.isArray( description )
					? { description }
					: { children: description };

				return (
					<Notifications.Notification
						id={ key }
						key={ key }
						{ ...entry }
						{ ...contentProps }
						dismissScreenReaderLabel="Dismiss"
						onDismiss={ () => {
							dismissNotification( key );
							if ( entry.onDismiss ) entry.onDismiss( key );
						} }
					/>
				);
			} ) }
		</Notifications>
	);
};

export default NotificationFeed;
