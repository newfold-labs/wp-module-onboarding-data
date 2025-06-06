// <reference types="Cypress" />
class performancePage {
	//Locators
	// in site-info box
	_visitSiteButton = '#wp-admin-bar-view-site a';
	// sample page link on front end of site
	_samplePageButton = '.wp-block-pages-list__item__link[href*="sample-page"]';
	_linkPrefetchText = '[data-cy="link-prefetch-settings"]';
	_dropDownForLinkPrefetch =
		'[data-cy="link-prefetch-behavior-desktop"] .nfd-select__button-label';
	// selected element in dropdown select
	_selectedDropDown = '[aria-selected="true"] .nfd-select__option-label';
	_excludeKeywordInputField = '[data-cy="link-prefetch-ignore-keywords"]';
	_desktopToggle = '[data-cy="link-prefetch-active-desktop-toggle"]';

	//All the methods related to performance page.
	getLinkPrefetchText() {
		return cy.get( this._linkPrefetchText );
	}

	getDropDownForLinkPrefetch() {
		return cy.get( this._dropDownForLinkPrefetch );
	}

	getVisitSiteButton() {
		return cy.get( this._visitSiteButton );
	}

	getSamplePageButton() {
		return cy.get( this._samplePageButton ).first();
	}

	getMouseHoverElement() {
		return cy.get( this._mouseHoverElement );
	}

	getMouseDownElement() {
		return cy.get( this._mouseDownElement );
	}

	getExcludeKeywordInputField() {
		return cy.get( this._excludeKeywordInputField );
	}

	getDesktopToggle() {
		return cy.get( this._desktopToggle );
	}

	getDropDownOptionLabel() {
		return cy.get( this._selectedDropDown );
	}

	getListItems() {
		return cy.get( 'ul.nfd-select__options > li' );
	}
	interceptRequest( method, url, alias ) {
		cy.intercept( method, url ).as( alias );
	}

	visitSamplePageAndIntercept( alias ) {
		// Wait for the sample page link to be visible
		this.getSamplePageButton()
			.should( 'be.visible' )
			.invoke( 'attr', 'href' )
			.then( ( samplePageUrl ) => {
				// Intercept the sample page URL with the given alias
				this.interceptRequest( 'GET', samplePageUrl, alias );

				// Reload the page after intercepting the URL
				cy.reload( true );
			} );
	}

	assertApiRequest( alias, expectedValue, checkType = 'status' ) {
		cy.wait( `@${ alias }` ).then( ( interception ) => {
			if ( checkType === 'status' ) {
				expect( interception.response.statusCode ).to.eq(
					expectedValue
				);
			} else if ( checkType === 'requestCount' ) {
				expect( interception.requestCount || 0 ).to.eq( expectedValue );
			}
		} );
	}

	extractSamplePageName( callback ) {
		this.getSamplePageButton()
			.should( 'be.visible' )
			.invoke( 'text' )
			.then( ( pageName ) => {
				cy.log( `Extracted Page Name: ${ pageName }` );
				callback( pageName.trim() ); // Use a callback to handle async execution
			} );
	}

	verifyIfLinkPreFetchIsDisplayed() {
		this.getLinkPrefetchText().scrollIntoView().should( 'be.visible' );
	}

	verifyIfToggleIsEnabled() {
		this.getDesktopToggle().then( ( $toggle ) => {
			if ( $toggle.attr( 'aria-checked' ) === 'false' ) {
				cy.wrap( $toggle ).click();
			}
		} );
		this.getDesktopToggle().should( 'have.attr', 'aria-checked', 'true' );
	}

	compareDropdownLabelAndSelectedOption() {
		// Get the text from the dropdown button label
		this.getDropDownForLinkPrefetch()
			.invoke( 'text' )
			.then( ( buttonLabel ) => {
				const trimmedButtonLabel = buttonLabel.trim();

				// Get the text from the selected option in the dropdown
				this.getDropDownOptionLabel()
					.invoke( 'text' )
					.should( 'not.be.empty' )
					.then( ( selectedOptionText ) => {
						const trimmedOptionText = selectedOptionText.trim();
						cy.log(
							`Dropdown Button Label: ${ trimmedButtonLabel }`
						);
						cy.log(
							`Selected Option Text: ${ trimmedOptionText }`
						);

						// Assert that both values are the same
						expect( trimmedButtonLabel ).to.eq( trimmedOptionText );
					} );
			} );
	}

	interceptCallForMouseDownWithoutExcludeRunTimeURL( statusCode ) {
		// Get the currently selected dropdown option
		this.getDropDownForLinkPrefetch()
			.invoke( 'text' )
			.should( 'not.be.empty' ) // Ensure dropdown text is present
			.then( ( selectedText ) => {
				selectedText = selectedText.trim();
				cy.log( `Currently selected option: ${ selectedText }` );

				// Open the dropdown and get all options
				this.getDropDownForLinkPrefetch().click();
				this.compareDropdownLabelAndSelectedOption();

				this.getListItems() // Get all options dynamically
					.should( 'have.length.at.least', 2 ) // Ensure there are at least two options
					.then( ( $options ) => {
						const optionsText = $options
							.map( ( i, el ) => Cypress.$( el ).text().trim() )
							.get();
						cy.log( `Available options: ${ optionsText }` );

						// Select the second option if it's different from the current one
						if ( selectedText !== optionsText[ 1 ] ) {
							cy.wrap( $options[ 1 ] ).click(); // Click on the second option

							// Verify the selection was updated correctly
							this.getDropDownForLinkPrefetch()
								.invoke( 'text' )
								.should( 'eq', optionsText[ 1 ] );
						} else {
							cy.log( 'Second option is already selected.' );
						}
					} );
			} );

		// Clear exclude keyword input field
		this.getExcludeKeywordInputField().clear();

		// Visit site
		this.getVisitSiteButton().click( { force: true } );

		// Handle sample page navigation
		this.getSamplePageButton( { timeout: 6000 } )
			.should( 'be.visible' )
			.invoke( 'attr', 'href' )
			.then( ( url ) => {
				const alias = 'apiRequest';
				this.visitSamplePageAndIntercept( alias );

				this.getSamplePageButton().click();
				this.assertApiRequest( alias, statusCode, 'status' );

				cy.go( 'back' ).go( 'back' );
			} );
	}

	interceptCallForMouseDownWithExcludeRunTimeURL( requestCount ) {
		// Get the currently selected dropdown option
		this.getDropDownForLinkPrefetch()
			.invoke( 'text' )
			.should( 'not.be.empty' ) // Ensure dropdown text is present
			.then( ( selectedText ) => {
				selectedText = selectedText.trim();
				cy.log( `Currently selected option: ${ selectedText }` );

				// Open the dropdown and get all options
				this.getDropDownForLinkPrefetch().click();
				this.compareDropdownLabelAndSelectedOption();

				this.getListItems() // Dynamically get all dropdown options
					.should( 'have.length.at.least', 2 ) // Ensure at least two options exist
					.then( ( $options ) => {
						const optionsText = $options
							.map( ( i, el ) => Cypress.$( el ).text().trim() )
							.get();
						cy.log( `Available options: ${ optionsText }` );

						// Select the second option (if it's different from the current one)
						if ( selectedText !== optionsText[ 1 ] ) {
							cy.wrap( $options[ 1 ] ).click(); // Click the second option

							// Verify that the dropdown selection is updated
							this.getDropDownForLinkPrefetch()
								.invoke( 'text' )
								.should( 'eq', optionsText[ 1 ] );
						} else {
							cy.log( 'Second option is already selected.' );
						}
					} );
			} );

		// Visit the site
		this.getVisitSiteButton().click( { force: true } );

		// Use the reusable function to extract the Sample Page Name
		this.extractSamplePageName( ( samplePageText ) => {
			cy.go( 'back' );

			this.getExcludeKeywordInputField()
				.clear()
				.type( samplePageText )
				.should( 'have.value', samplePageText );

			this.getVisitSiteButton().click( { force: true } );

			// Intercept the API call related to the sample page
			const alias = 'apiRequest';
			this.visitSamplePageAndIntercept( alias ); // Using your existing method for intercepting the request

			// Perform Mouse Down on the Sample Page Button
			this.getSamplePageButton().click();

			// Assert API request count
			this.assertApiRequest( alias, requestCount, 'requestCount' );

			cy.go( 'back' ).go( 'back' );
		} );
	}
	interceptCallForMouseHoverWithExcludeRunTimeURL( requestCount ) {
		const forceReload = true;

		// Get the currently selected dropdown option
		this.getDropDownForLinkPrefetch()
			.invoke( 'text' )
			.should( 'not.be.empty' )
			.then( ( selectedText ) => {
				selectedText = selectedText.trim();
				cy.log( `Currently selected option: ${ selectedText }` );

				// Open dropdown and check available options
				this.getDropDownForLinkPrefetch().click();
				this.compareDropdownLabelAndSelectedOption();

				this.getListItems()
					.should( 'have.length.at.least', 2 )
					.then( ( $options ) => {
						const firstOption = Cypress.$( $options[ 0 ] )
							.text()
							.trim();
						cy.log( `First dropdown option: ${ firstOption }` );

						if ( selectedText !== firstOption ) {
							cy.wrap( $options[ 0 ] ).click();
							this.getDropDownForLinkPrefetch()
								.invoke( 'text' )
								.should( 'eq', firstOption );
						} else {
							cy.log( 'First option is already selected.' );
						}
					} );
			} );

		// Visit the site
		this.getVisitSiteButton().click( { force: true } );

		// Extract Sample Page Name & Continue Actions
		this.getSamplePageButton()
			.invoke( 'prop', 'href' )
			.then( ( url ) => {
				const pageName = url.split( '/' ).filter( Boolean ).pop();
				cy.log( `Extracted page name: ${ pageName }` );
				expect( pageName ).to.not.be.empty;

				cy.go( 'back' );

				// Enter extracted page name into Exclude Keywords field
				this.getExcludeKeywordInputField()
					.clear()
					.type( pageName )
					.should( 'have.value', pageName );

				// Revisit site after setting the exclusion keyword
				this.getVisitSiteButton().click( { force: true } );

				cy.reload( forceReload );

				// Intercept API call for the sample page
				cy.intercept( 'GET', url ).as( 'apiRequest' );

				// Perform Mouse Hover on the Sample Page Button
				this.getSamplePageButton().trigger( 'mouseover' );

				// Assert API request count
				cy.wrap( requestCount ).should( 'equal', 0 );

				// Navigate back twice to return to the original page
				cy.go( 'back' ).then( () => {
					cy.go( 'back' );
				} );
			} );
	}
}

export default performancePage;
