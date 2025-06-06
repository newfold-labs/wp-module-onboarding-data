import * as actions from './actions';
import * as selectors from './selectors';
import { createReduxStore, register } from '@wordpress/data';
import { STORE_NAME } from '../data/constants';
import reducer from './reducer';

export const performanceStoreConfig = {
	reducer,
	actions,
	selectors,
};

export const store = createReduxStore( STORE_NAME, performanceStoreConfig );
register( store );
