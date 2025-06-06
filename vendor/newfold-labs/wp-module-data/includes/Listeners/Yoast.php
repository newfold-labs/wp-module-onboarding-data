<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors Yoast events
 */
class Yoast extends Listener {
	// We don't want to track these fields
	private $site_representation_skip_fields = array( 'company_logo_id', 'person_logo_id', 'description' );

	// The names used for Hiive events tracking are different from the names used for the Yoast options
	private $site_representation_map = array(
		'company_or_person'         => 'site_representation',
		'company_name'              => 'organization_name',
		'company_logo'              => 'organization_logo',
		'person_logo'               => 'logo',
		'company_or_person_user_id' => 'name',
		'website_name'              => 'website_name',
	);

	private $social_profiles_map = array(
		'facebook_site'     => 'facebook_profile',
		'twitter_site'      => 'twitter_profile',
		'other_social_urls' => 'other_profiles',
	);

	/**
	 * Register the hooks for the listener
	 *
	 * @return void
	 */
	public function register_hooks() {
		// First time configuration
		add_action( 'wpseo_ftc_post_update_site_representation', array( $this, 'site_representation_updated' ), 10, 3 );
		add_action( 'wpseo_ftc_post_update_social_profiles', array( $this, 'social_profiles_updated' ), 10, 3 );
		add_action( 'wpseo_ftc_post_update_enable_tracking', array( $this, 'tracking_updated' ), 10, 3 );
	}

	/**
	 * The user just updated their site representation
	 *
	 * @param array $new_values The new values for the options related to the site representation
	 * @param array $old_values The old values for the options related to the site representation
	 * @param array $failures   The failures that occurred during the update
	 *
	 * @return void
	 */
	public function site_representation_updated( $new_values, $old_values, $failures ) {
		// All the options are unchanged, opt out
		if ( $new_values === $old_values ) {
			return;
		}

		$mapped_new_values = $this->map_params_names_to_hiive_names( $new_values, $this->site_representation_map, $this->site_representation_skip_fields );
		$mapped_old_values = $this->map_params_names_to_hiive_names( $old_values, $this->site_representation_map, $this->site_representation_skip_fields );
		$mapped_failures   = $this->map_failures_to_hiive_names( $failures, $this->site_representation_map, $this->site_representation_skip_fields );

		foreach ( $mapped_new_values as $key => $value ) {
			$this->maybe_push_site_representation_event( $key, $value, $mapped_old_values[ $key ], \in_array( $key, $mapped_failures ) );
		}
	}

	/**
	 * The user just updated their personal profiles
	 *
	 * @param array $new_values The new values for the options related to the site representation
	 * @param array $old_values The old values for the options related to the site representation
	 * @param array $failures   The failures that occurred during the update
	 *
	 * @return void
	 */
	public function social_profiles_updated( $new_values, $old_values, $failures ) {
		// Yoast stores only twitter username, and $new_values stores the pre-processed values
		if ( strpos( $new_values['twitter_site'], 'twitter.com/' ) !== false ) {
			$new_values['twitter_site'] = ( explode( 'twitter.com/', $new_values['twitter_site'] )[1] );
		}

		// All the options are unchanged, opt out
		if ( $new_values === $old_values ) {
			return;
		}

		// Remove multiple occurences of other_social_urls;
		$cleaned_failures = $this->clean_social_profiles_failures( $failures );

		$mapped_values     = $this->map_params_names_to_hiive_names( $new_values, $this->social_profiles_map );
		$mapped_old_values = $this->map_params_names_to_hiive_names( $old_values, $this->social_profiles_map );
		$mapped_failures   = $this->map_failures_to_hiive_names( $cleaned_failures, $this->social_profiles_map );

		foreach ( $mapped_values as $key => $value ) {
			// The option update failed
			if ( \in_array( $key, $mapped_failures ) ) {
				$this->push( "failed_$key", array( 'category' => 'ftc_personal_profiles' ) );
				return;
			}

			if ( $value !== $mapped_old_values[ $key ] ) {
				$this->maybe_push_social_profiles_event( $key, $value, $mapped_old_values[ $key ], \in_array( $key, $mapped_failures ) );
			}
		}
	}

	/**
	 * The user updated their tracking preferences
	 *
	 * @param string $new_value The new value for the option related to tracking
	 * @param string $old_value The old value for the option related to tracking
	 * @param bool   $failed    Whether the option update failed
	 *
	 * @return void
	 */
	public function tracking_updated( $new_value, $old_value, $failed ) {
		// Option unchanged, opt out
		if ( $new_value === $old_value ) {
			return;
		}

		$failed ? $this->push( 'failed_usage_tracking', array( 'category' => 'ftc_tracking' ) ) : $this->push( 'changed_usage_tracking', array( 'category' => 'ftc_tracking' ) );
	}

	/**
	 * A method used to (maybe) push a site representation-related event to the queue.
	 *
	 * @param string $key       The option key
	 * @param string $value     The new option value
	 * @param string $old_value The old option value
	 * @param bool   $failure   Whether the option update failed
	 *
	 * @return void
	 */
	private function maybe_push_site_representation_event( $key, $value, $old_value, $failure ) {
		$category = 'ftc_site_representation';

		// The option update failed
		if ( $failure ) {
			$this->push( "failed_$key", array( 'category' => $category ) );
			return;
		}

		// The option value changed
		if ( $value !== $old_value ) {
			// The option was set for the first time

			// name is a special case, because it represents the company_or_person_user_id which is initialised to false, and the first time the user saves the site representation step
			// is set either to 0 if the site represents an organisation, or to an integer > 0 if the site represents a person
			if ( $key === 'name' ) {
				if ( $old_value === false && $value === 0 ) {
					return;
				}
			}

			// Again, name is a special case, because if its old value was 0 and a value different that 0 is being received, it means that the user
			// switched from organisation to person, and then the person id is being set.
			// Once the name is assigned an integer > 0, it can never go back to 0, even if the user switches back to organisation
			// ( it "caches" the last user id that was set)
			if ( ( $this->is_param_empty( $old_value ) ) || ( $key === 'name' && $old_value === 0 ) ) {
				$this->push( "set_$key", array( 'category' => $category ) );
				return;
			}

			// The option was updated
			$data = array(
				'category' => $category,
				'data'     => array(
					'label_key' => $key,
					'new_value' => $value,
				),
			);

			$this->push(
				"changed_$key",
				$data
			);
		}
	}

	/**
	 * A method used to (maybe) push a social profile-related event to the queue.
	 *
	 * @param string $key       The option key
	 * @param string $value     The new option value
	 * @param string $old_value The old option value
	 * @param bool   $failure   Whether the option update failed
	 *
	 * @return void
	 */
	private function maybe_push_social_profiles_event( $key, $value, $old_value, $failure ) {
		$category = 'ftc_personal_profiles';

		// The option update failed
		if ( $failure ) {
			$this->push( "failed_$key", array( 'category' => $category ) );
			return;
		}

		// The option value changed
		if ( $value !== $old_value ) {
			if ( $key === 'other_profiles' ) {
				$this->push_other_social_profiles( $key, $value, $old_value, $category );
				return;
			}

			// The option was set for the first time
			if ( $this->is_param_empty( $old_value ) ) {
				$this->push( "set_$key", array( 'category' => $category ) );
				return;
			}

			// The option was updated
			$this->push( "changed_$key", array( 'category' => $category ) );
		}
	}


	/**
	 * A method used to (maybe) push the other_profiles-related event to the queue.
	 *
	 * @param string $key       The option key (other_profiles)
	 * @param array  $new_value The array of new social profiles
	 * @param array  $old_value The array of old social profiles
	 * @param string $category  The category of the event
	 *
	 * @return void
	 */
	private function push_other_social_profiles( $key, $new_value, $old_value, $category ) {
		// The option was set for the first time
		if ( $this->is_param_empty( $old_value ) ) {
			$this->push( "set_$key", array( 'category' => $category ) );
			return;
		}

		$changed_profiles = \array_map(
			function ( $value ) {
				return $this->get_base_url( \wp_unslash( $value ) );
			},
			$new_value
		);

		// The option was updated
		$data = array(
			'category' => $category,
			'data'     => array(
				'label_key' => $key,
				'new_value' => $changed_profiles,
			),
		);

		$this->push( 'changed_other_profiles', $data );
	}

	/**
	 * Maps the param names to the names used for Hiive events tracking.
	 *
	 * @param array $params      The params to map.
	 * @param array $map         The map to use.
	 * @param array $skip_fields The fields to skip.
	 *
	 * @return array The mapped params.
	 */
	private function map_params_names_to_hiive_names( $params, $map, $skip_fields = array() ) {
		$mapped_params = array();

		foreach ( $params as $param_name => $param_value ) {
			if ( in_array( $param_name, $skip_fields, true ) ) {
				continue;
			}

			$new_name                   = $map[ $param_name ];
			$mapped_params[ $new_name ] = $param_value;
		}

		return $mapped_params;
	}

	/**
	 * Maps the names of the params which failed the update to the names used for Hiive events tracking.
	 *
	 * @param array $failures    The params names to map.
	 * @param array $map         The map to use.
	 * @param array $skip_fields The fields to skip.
	 *
	 * @return array The mapped params names.
	 */
	private function map_failures_to_hiive_names( $failures, $map, $skip_fields = array() ) {
		$mapped_failures = array();

		foreach ( $failures as $failed_field_name ) {
			if ( in_array( $failed_field_name, $skip_fields, true ) ) {
				continue;
			}

			$mapped_failures[] = $map[ $failed_field_name ];
		}

		return $mapped_failures;
	}

	/**
	 * Checks whether a param is empty.
	 *
	 * @param mixed $param The param to check.
	 *
	 * @return bool Whether the param is empty.
	 */
	private function is_param_empty( $param ) {
		if ( is_array( $param ) ) {
			return ( count( $param ) === 0 );
		}

		return ( strlen( $param ) === 0 );
	}

	/**
	 * Gets the base url of a given url.
	 *
	 * @param string $url The url.
	 *
	 * @return string The base url.
	 */
	private function get_base_url( $url ) {
		$parts = \parse_url( $url );

		return $parts['scheme'] . '://' . $parts['host'];
	}

	/**
	 * Removes multiple occurences of other_social_urls from the failures array
	 *
	 * @param array $failures The failures array
	 *
	 * @return array The cleaned failures array
	 */
	private function clean_social_profiles_failures( $failures ) {
		$cleaned_failures             = array();
		$other_social_profiles_failed = false;

		foreach ( $failures as $failure ) {
			if ( strpos( $failure, 'other_social_urls' ) === 0 ) {
				$other_social_profiles_failed = true;
				continue;
			}
			$cleaned_failures[] = $failure;
		}

		if ( $other_social_profiles_failed ) {
			$cleaned_failures[] = 'other_social_urls';
		}

		return $cleaned_failures;
	}
}
