// Newfold
import { Container, Root, Page } from '@newfold/ui-component-library';

// Components
import CacheSettings from '../../sections/CacheSettings';
import NotificationFeed from '../NotificationFeed';
import CacheExclusion from '../../sections/CacheExclusion';
import ClearCache from '../../sections/ClearCache';
import Skip404 from '../../sections/Skip404';
import JetpackBoost from '../../sections/JetpackBoost';
import LinkPrefetch from '../../sections/LinkPrefetch';
import ImageOptimization from '../../sections/ImageOptimization';

import getAppText from './getAppText';

const App = () => {
	const { title, description } = getAppText();

	return (
		<Root context={ { isRTL: false } }>
			<NotificationFeed />
			<Page title={ title }>
				<Container>
					<Container.Header
						title={ title }
						description={ description }
						className="wppbh-app-settings-header"
					/>
					<Container.Block
						separator
						className="newfold-cache-settings"
					>
						<CacheSettings />
					</Container.Block>
					<Container.Block
						separator
						className="newfold-cache-exclusion"
					>
						<CacheExclusion />
					</Container.Block>
					<Container.Block separator className="newfold-clear-cache">
						<ClearCache />
					</Container.Block>
					<Container.Block separator className="newfold-skip404">
						<Skip404 />
					</Container.Block>
					<Container.Block
						separator
						className="newfold-performance-advanced-settings"
					>
						<JetpackBoost />
					</Container.Block>
					<Container.Block
						separator
						className="newfold-link-prefetch"
					>
						<LinkPrefetch />
					</Container.Block>
					<Container.Block className="newfold-image-optimization">
						<ImageOptimization />
					</Container.Block>
				</Container>
			</Page>
		</Root>
	);
};

export default App;
