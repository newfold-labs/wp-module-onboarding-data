export const setCacheLevel = ( level ) => ( {
	type: 'SET_CACHE_LEVEL',
	level,
} );

export const pushNotification = ( id, message ) => ( {
	type: 'PUSH_NOTIFICATION',
	id,
	message,
} );

export const dismissNotification = ( id ) => ( {
	type: 'DISMISS_NOTIFICATION',
	id,
} );
