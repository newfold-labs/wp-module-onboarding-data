import './image-bulk-optimizer.css';

document.addEventListener( 'DOMContentLoaded', () => {
	const { __ } = wp.i18n;

	const bulkOptimizeButtonId = 'nfd-bulk-optimize-btn';
	let cancelRequested = false;

	const bulkSelectButtonClasses = [
		'button',
		'media-button',
		'select-mode-toggle-button',
	];
	const deletePermanentlyButtonClasses = [
		'button',
		'media-button',
		'button-primary',
		'button-large',
		'delete-selected-button',
	];

	const removeBulkOptimizeButton = () => {
		const bulkOptimizeButton =
			document.getElementById( bulkOptimizeButtonId );
		if ( bulkOptimizeButton ) bulkOptimizeButton.remove();
	};

	const createModal = () => {
		const modal = document.createElement( 'div' );
		modal.id = 'nfd-bulk-modal';
		modal.className = 'nfd-performance-image-modal';

		const modalContent = document.createElement( 'div' );
		modalContent.className = 'nfd-performance-image-modal-content';

		const modalTitle = document.createElement( 'h2' );
		modalTitle.id = 'nfd-modal-title';
		modalTitle.textContent = __(
			'Optimizing Images…',
			'wp-module-performance'
		);

		const currentFileName = document.createElement( 'p' );
		currentFileName.id = 'nfd-current-file';
		currentFileName.textContent = __(
			'Preparing files…',
			'wp-module-performance'
		);

		const progressContainer = document.createElement( 'div' );
		progressContainer.id = 'nfd-progress-container';
		progressContainer.className =
			'nfd-performance-image-progress-container';

		const progressBar = document.createElement( 'div' );
		progressBar.id = 'nfd-progress-bar';
		progressBar.className = 'nfd-performance-image-progress-bar';

		const resultList = document.createElement( 'ul' );
		resultList.id = 'nfd-result-list';
		resultList.className = 'nfd-performance-image-result-list';

		const doneButton = document.createElement( 'button' );
		doneButton.textContent = __( 'Done', 'wp-module-performance' );
		doneButton.className =
			'button button-secondary nfd-performance-image-done-button';
		doneButton.style.display = 'none'; // Hidden initially
		doneButton.addEventListener( 'click', () => {
			modal.remove();
			window.location.reload();
		} );

		progressContainer.appendChild( progressBar );
		modalContent.append(
			modalTitle,
			currentFileName,
			progressContainer,
			resultList,
			doneButton
		);
		modal.appendChild( modalContent );
		document.body.appendChild( modal );

		return {
			modal,
			progressBar,
			modalTitle,
			currentFileName,
			resultList,
			doneButton,
			progressContainer,
		};
	};

	const openModal = () => {
		cancelRequested = false;
		const {
			progressBar,
			modalTitle,
			currentFileName,
			resultList,
			doneButton,
			progressContainer,
		} = createModal();
		progressBar.style.width = '0%';
		currentFileName.textContent = '';
		return {
			progressBar,
			modalTitle,
			currentFileName,
			resultList,
			doneButton,
			progressContainer,
		};
	};

	const getFileName = ( attachment ) => {
		return attachment.getAttribute( 'aria-label' );
	};

	const handleBulkOptimize = async () => {
		const selectedItems = Array.from(
			document.querySelectorAll( '.attachment.selected' )
		).map( ( attachment ) => ( {
			id: attachment.getAttribute( 'data-id' ),
			name: getFileName( attachment ),
		} ) );

		if ( ! selectedItems.length ) return;

		const apiUrl =
			window.nfdPerformance?.imageOptimization?.bulkOptimizer?.apiUrl;

		if ( ! apiUrl ) {
			return;
		}

		const {
			progressBar,
			modalTitle,
			currentFileName,
			resultList,
			doneButton,
			progressContainer,
		} = openModal();
		const results = [];

		try {
			for ( let i = 0; i < selectedItems.length; i++ ) {
				if ( cancelRequested ) {
					modalTitle.textContent = __(
						'Optimization Canceled',
						'wp-module-performance'
					);
					break;
				}

				const { id: mediaId, name: fileName } = selectedItems[ i ];
				currentFileName.textContent =
					__( 'Optimizing:', 'wp-module-performance' ) +
					` ${ fileName }`;

				try {
					await wp.apiFetch( {
						url: apiUrl,
						method: 'POST',
						data: { media_id: parseInt( mediaId, 10 ) },
					} );

					results.push( { name: fileName, status: 'passed' } );
				} catch ( error ) {
					results.push( { name: fileName, status: 'failed' } );
				}

				const progress = ( ( i + 1 ) / selectedItems.length ) * 100;
				progressBar.style.width = `${ progress }%`;
			}

			modalTitle.textContent = __(
				'Optimization Complete!',
				'wp-module-performance'
			);
			progressContainer.style.display = 'none';
			currentFileName.style.display = 'none';

			results.forEach( ( { name, status } ) => {
				const listItem = document.createElement( 'li' );
				const statusText =
					status === 'passed'
						? __( 'Passed', 'wp-module-performance' )
						: __( 'Failed', 'wp-module-performance' );

				listItem.textContent = `${ name } - ${ statusText }`;
				resultList.appendChild( listItem );
			} );

			doneButton.style.display = 'block';
		} catch ( error ) {
			modalTitle.textContent = __(
				'An error occurred.',
				'wp-module-performance'
			);
		}
	};

	const createBulkOptimizeButton = () => {
		const bulkOptimizeButton = document.createElement( 'button' );
		bulkOptimizeButton.id = bulkOptimizeButtonId;
		bulkOptimizeButton.className =
			'button media-button button-large button-primary';
		bulkOptimizeButton.textContent = __(
			'Optimize',
			'wp-module-performance'
		);
		bulkOptimizeButton.disabled = true;
		bulkOptimizeButton.addEventListener( 'click', handleBulkOptimize );
		return bulkOptimizeButton;
	};

	const addBulkOptimizeButton = () => {
		if ( document.getElementById( bulkOptimizeButtonId ) ) return;

		const deletePermanentlyButton = document.querySelector(
			'.button.media-button.button-primary.button-large.delete-selected-button'
		);

		if (
			! hasExactClassList(
				deletePermanentlyButton,
				deletePermanentlyButtonClasses
			)
		)
			return;

		const bulkOptimizeButton = createBulkOptimizeButton();
		deletePermanentlyButton.parentElement.insertBefore(
			bulkOptimizeButton,
			deletePermanentlyButton.nextSibling
		);

		monitorSelectedItems( bulkOptimizeButton );
	};

	const monitorSelectedItems = ( bulkOptimizeButton ) => {
		const updateButtonState = () => {
			const hasSelectedItems =
				document.querySelectorAll( '.attachment.selected' ).length > 0;
			bulkOptimizeButton.disabled = ! hasSelectedItems;
		};

		const mediaFrameContent = document.querySelector(
			'.media-frame-content'
		);
		if ( mediaFrameContent ) {
			const observer = new MutationObserver( updateButtonState );
			observer.observe( mediaFrameContent, {
				childList: true,
				subtree: true,
			} );
			updateButtonState();
		}
	};

	const hasExactClassList = ( element, classList ) =>
		element?.classList.length === classList.length &&
		classList.every( ( cls ) => element.classList.contains( cls ) );

	// Automatically select the Bulk Select button if the URL parameter is set
	const urlParams = new URLSearchParams( window.location.search );
	const autoSelect = urlParams.get( 'autoSelectBulk' );

	if ( autoSelect === 'true' ) {
		const observer = new MutationObserver( () => {
			const bulkSelectButton = document.querySelector(
				'.button.media-button.select-mode-toggle-button'
			);

			if ( bulkSelectButton ) {
				bulkSelectButton.click();
				observer.disconnect();
			}
		} );

		observer.observe( document.body, { childList: true, subtree: true } );
	}

	const observer = new MutationObserver( () => {
		const bulkSelectButton = document.querySelector(
			'.button.media-button.select-mode-toggle-button'
		);

		const isBulkSelectButtonVisible = hasExactClassList(
			bulkSelectButton,
			bulkSelectButtonClasses
		);
		if ( isBulkSelectButtonVisible ) {
			removeBulkOptimizeButton();
		} else {
			addBulkOptimizeButton();
		}
	} );

	observer.observe( document.body, { childList: true, subtree: true } );
} );
