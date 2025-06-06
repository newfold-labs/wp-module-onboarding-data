document.addEventListener( 'DOMContentLoaded', () => {
	const testLink = document.createElement( 'link' );
	const supportsPrefetch = testLink.relList?.supports?.( 'prefetch' );
	const isDataSaver =
		navigator.connection?.saveData ||
		navigator.connection?.effectiveType?.includes( '2g' );
	const supportsIntersectionObserver =
		'IntersectionObserver' in window &&
		'isIntersecting' in IntersectionObserverEntry.prototype;

	if ( ! supportsPrefetch || isDataSaver ) {
		return;
	}

	class LinkPrefetcher {
		constructor( config ) {
			Object.assign( this, {
				activeOnDesktop: config.activeOnDesktop,
				behavior: config.behavior,
				hoverDelay: parseInt( config.hoverDelay ) || 60,
				ignoreKeywords: config.ignoreKeywords
					? config.ignoreKeywords.split( ',' )
					: '',
				instantClick: config.instantClick,
				mobileActive: config.activeOnMobile,
				isMobile: config.isMobile,
				mobileBehavior: config.mobileBehavior,
				prefetchedUrls: new Set(),
				eventOptions: { capture: true, passive: true },
				timerId: null,
			} );
		}

		init() {
			const isChrome = navigator.userAgent.includes( 'Chrome/' );
			const chromeVersion =
				isChrome &&
				parseInt( navigator.userAgent.split( 'Chrome/' )[ 1 ] );

			if ( isChrome && chromeVersion < 110 ) {
				return;
			}
			if (
				( this.isMobile && ! this.mobileActive ) ||
				( ! this.isMobile && ! this.activeOnDesktop )
			) {
				return;
			}

			if ( ! this.isMobile ) {
				this.behavior === 'mouseHover' &&
					document.addEventListener(
						'mouseover',
						this.handleHover.bind( this ),
						this.eventOptions
					);
				this.behavior === 'mouseDown' &&
					document.addEventListener(
						'mousedown',
						this.instantClick
							? this.handleInstantClick.bind( this )
							: this.handleMouseDown.bind( this ),
						this.eventOptions
					);
			}

			if (
				this.isMobile &&
				this.mobileBehavior === 'viewport' &&
				supportsIntersectionObserver
			) {
				this.setupViewportObserver();
			}
		}

		setupViewportObserver() {
			const observer = new IntersectionObserver( ( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting ) {
						observer.unobserve( entry.target );
						this.prefetchIfEligible( entry.target.href ) &&
							this.prefetchIt( entry.target.href );
					}
				} );
			} );

			const idleCallback =
				window.requestIdleCallback || ( ( cb ) => setTimeout( cb, 1 ) );
			idleCallback( () =>
				setTimeout(
					() =>
						document
							.querySelectorAll( 'a' )
							.forEach( ( a ) => observer.observe( a ) ),
					1000
				)
			);
		}

		handleMouseDown( event ) {
			const el = event.target.closest( 'a' );
			this.prefetchIfEligible( el?.href ) && this.prefetchIt( el?.href );
		}

		handleInstantClick( event ) {
			const el = event.target.closest( 'a' );
			if ( ! el || event.which > 1 || event.metaKey || event.ctrlKey ) {
				return;
			}
			el.addEventListener(
				'click',
				( e ) => e.detail !== 'instantClick' && e.preventDefault(),
				{ capture: true, passive: false, once: true }
			);
			el.dispatchEvent(
				new MouseEvent( 'click', {
					bubbles: true,
					cancelable: false,
					detail: 'instantClick',
				} )
			);
		}

		handleHover( event ) {
			const link = event.target.closest( 'a' );
			if ( ! link || ! this.prefetchIfEligible( link.href ) ) {
				return;
			}
			link.addEventListener(
				'mouseout',
				() => clearTimeout( this.timerId ),
				this.eventOptions
			);
			this.timerId = setTimeout(
				() => this.prefetchIt( link.href ),
				this.hoverDelay
			);
		}

		prefetchIfEligible( url ) {
			if ( ! url || this.prefetchedUrls.has( url ) ) {
				return false;
			}

			if (
				url.replace( /\/$/, '' ) ===
					location.href.replace( /\/$/, '' ) ||
				( this.ignoreKeywords !== '' &&
					this.ignoreKeywords.some( ( k ) => url.includes( k ) ) )
			) {
				return false;
			}

			return true;
		}

		prefetchIt( url ) {
			const link = document.createElement( 'link' );
			link.rel = 'prefetch';
			link.href = url;
			document.head.appendChild( link );
			this.prefetchedUrls.add( url );
		}
	}

	const config = window.LP_CONFIG || {
		activeOnDesktop: true,
		behavior: 'mouseHover',
		hoverDelay: 60,
		instantClick: true,
		activeOnMobile: true,
		mobileBehavior: 'viewport',
		ignoreKeywords: '#,?',
	};

	new LinkPrefetcher( config ).init();
} );
