{
	const { __, sprintf } = wp.i18n;

	/**
	 * Helper method to check if a feature is enabled by name
	 *
	 * @param {*} name
	 * @return {boolean} whether the feature is enabled
	 */
	const isEnabled = async ( name ) => {
		return window.NewfoldFeatures.features[ name ];
	};

	/**
	 * Helper method to enable a feature by name
	 * @param {*} name
	 * @return {*} {success:boolean and message:string}
	 */
	const enable = async ( name ) => {
		const result = {};
		if ( await isEnabled( name ) ) {
			result.success = false;
			result.message = sprintf( __( "'%s' is already enabled.", 'wp-module-features' ), name );
			return result;
		}

		await window.wp
			.apiFetch( {
				url: `${ window.NewfoldFeatures.restUrl }/feature/enable?feature=${ name }`,
				method: 'POST',
			} )
			.then( ( response ) => {
				if ( response === true ) {
					updateFeature( name, true );
					result.success = true;
					result.message = sprintf( __( "'%s' is now enabled", 'wp-module-features' ), name );
				} else {
					result.success = false;
					result.message = sprintf( __( "'%s' could not be enabled", 'wp-module-features' ), name );
				}
			} )
			.catch( ( error ) => {
				result.success = false;
				result.message = error.message;
			} );

		return result;
	};

	/**
	 * Helper method to disable a feature by name
	 * @param {*} name
	 * @return {*} {success:boolean and message:string}
	 */
	const disable = async ( name ) => {
		const result = {};
		if ( false === ( await isEnabled( name ) ) ) {
			result.success = false;
			result.message = sprintf( __( "'%s' is already disabled.", 'wp-module-features' ), name );
			return result;
		}

		await window.wp
			.apiFetch( {
				url: `${ window.NewfoldFeatures.restUrl }/feature/disable?feature=${ name }`,
				method: 'POST',
			} )
			.then( ( response ) => {
				if ( response === true ) {
					updateFeature( name, false );
					result.success = true;
					result.message = sprintf( __( "'%s' is now disabled", 'wp-module-features' ), name );
				} else {
					result.success = false;
					result.message = sprintf( __( "'%s' could not be disabled", 'wp-module-features' ), name );
				}
			} )
			.catch( ( error ) => {
				result.success = false;
				result.message = error.message;
			} );

		return result;
	};

	/**
	 * Update a feature in the object
	 * @param {string}  name
	 * @param {boolean} value
	 */
	const updateFeature = ( name, value ) => {
		window.NewfoldFeatures.features[ name ] = value;
	};

	// Set up the localized features object with methods
	const methods = {
		isEnabled,
		enable,
		disable,
	};
	window.NewfoldFeatures = { ...window.NewfoldFeatures, ...methods };
}
