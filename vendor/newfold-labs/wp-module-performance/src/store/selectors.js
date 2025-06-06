export const getCacheLevel = ( state ) => state.cacheLevel;

export const getNotifications = ( state ) => state.feed;

export const getVisibleNotifications = ( state ) =>
	Object.entries( state.feed )
		.filter( ( [ , value ] ) => value !== null )
		.map( ( [ key, value ] ) => [ key, value ] );
