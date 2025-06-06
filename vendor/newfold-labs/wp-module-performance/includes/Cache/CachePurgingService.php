<?php

namespace NewfoldLabs\WP\Module\Performance\Cache;

use NewfoldLabs\WP\Module\Performance\Performance;
use NewfoldLabs\WP\Module\Performance\Cache\Types\CacheBase;
use wpscholar\Url;

use function NewfoldLabs\WP\Module\Performance\to_studly_case;
use function NewfoldLabs\WP\Module\Performance\to_snake_case;

/**
 * Cache purging service.
 */
class CachePurgingService {

	/**
	 * Define cache types.
	 *
	 * @var array|CacheBase[] $cache_types Cache types.
	 */
	public $cache_types = array();

	/**
	 * Constructor.
	 *
	 * @param CacheBase[] $cache_types Cache types.
	 */
	public function __construct( array $cache_types ) {

		$this->cache_types = $cache_types;

		if ( $this->can_purge() ) {

			// Handle manual purge requests
			add_action( 'init', array( $this, 'manual_purge_request' ) );

			// Handle automatic purging
			add_action( 'transition_post_status', array( $this, 'on_save_post' ), 10, 3 );
			add_action( 'edit_terms', array( $this, 'on_edit_term' ) );
			add_action( 'comment_post', array( $this, 'on_update_comment' ) );
			add_action( 'updated_option', array( $this, 'on_update_option' ), 10, 3 );
			add_action( 'wp_update_nav_menu', array( $this, 'purge_all' ) );

		}
	}

	/**
	 * Check if the cache can be purged.
	 *
	 * @return bool
	 */
	public function can_purge() {
		foreach ( $this->cache_types as $instance ) {
			if ( array_key_exists( Purgeable::class, class_implements( $instance ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Listens for purge actions and handles based on type.
	 */
	public function manual_purge_request() {

		$purge_all = Performance::PURGE_ALL;
		$purge_url = Performance::PURGE_URL;

		if ( ( isset( $_GET[ $purge_all ] ) || isset( $_GET[ $purge_url ] ) ) && is_user_logged_in() && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$url = new Url();
			$url->removeQueryVar( $purge_all );
			$url->removeQueryVar( $purge_url );

			if ( isset( $_GET[ $purge_all ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$this->purge_all();
			} else {
				$this->purge_url( Url::stripQueryString( $url ) );
			}
			wp_safe_redirect(
				$url,
				302,
				'Newfold File Caching'
			);
			exit;
		}
	}

	/**
	 * Purge everything.
	 */
	public function purge_all() {
		foreach ( $this->cache_types as $instance ) {
			if ( array_key_exists( Purgeable::class, class_implements( $instance ) ) ) {
				/**
				 * Purgeable instance.
				 *
				 * @var Purgeable $instance
				 */
				$instance->purge_all();
			}
		}
	}

	/**
	 * Purge a specific URL.
	 *
	 * @param  string $url  The URL to be purged.
	 */
	public function purge_url( $url ) {
		foreach ( $this->cache_types as $instance ) {
			if ( array_key_exists( Purgeable::class, class_implements( $instance ) ) ) {
				/**
				 * Purgeable instance.
				 *
				 * @var Purgeable $instance
				 */
				$instance->purge_url( $url );
			}
		}
	}

	/**
	 * Purge appropriate caches when a post is updated.
	 *
	 * @param  string   $oldStatus  The previous post status
	 * @param  string   $newStatus  The new post status
	 * @param  \WP_Post $post  The post object of the edited or created post
	 */
	public function on_save_post( $oldStatus, $newStatus, \WP_Post $post ) {

		// Skip purging for non-public post types
		if ( ! get_post_type_object( $post->post_type )->public ) {
			return;
		}

		// Skip purging if the post wasn't public before and isn't now
		if ( 'publish' !== $oldStatus && 'publish' !== $newStatus ) {
			return;
		}

		// Purge post URL when post is updated.
		$permalink = get_permalink( $post );
		if ( $permalink ) {
			$this->purge_url( $permalink );
		}

		// Purge taxonomy term URLs for related terms.
		$taxonomies = get_post_taxonomies( $post );
		foreach ( $taxonomies as $taxonomy ) {
			if ( $this->is_public_taxonomy( $taxonomy ) ) {
				$terms = get_the_terms( $post, $taxonomy );
				if ( is_array( $terms ) ) {
					foreach ( $terms as $term ) {
						$term_link = get_term_link( $term );
						$this->purge_url( $term_link );
					}
				}
			}
		}

		// Purge post type archive URL when post is updated.
		$post_type_archive = get_post_type_archive_link( $post->post_type );
		if ( $post_type_archive ) {
			$this->purge_url( $post_type_archive );
		}

		// Purge date archive URL when post is updated.
		$year_archive = get_year_link( (int) get_the_date( 'y', $post ) );
		$this->purge_url( $year_archive );
	}

	/**
	 * Purge taxonomy term URL when a term is updated.
	 *
	 * @param  int $termId  Term ID
	 */
	public function on_edit_term( $termId ) {
		$url = get_term_link( $termId );
		if ( ! is_wp_error( $url ) ) {
			$this->purge_url( $url );
		}
	}

	/**
	 * Purge a single post when a comment is updated.
	 *
	 * @param  int $commentId  ID of the comment.
	 */
	public function on_update_comment( $commentId ) {
		$comment = get_comment( $commentId );
		if ( $comment && property_exists( $comment, 'comment_post_ID' ) ) {
			$postUrl = get_permalink( $comment->comment_post_ID );
			if ( $postUrl ) {
				$this->purge_url( $postUrl );
			}
		}
	}

	/**
	 * Purge all caches when an option is updated.
	 *
	 * @param  string $option    Option name.
	 * @param  mixed  $oldValue  Old option value.
	 * @param  mixed  $newValue  New option value.
	 *
	 * @return bool
	 */
	public function on_update_option( $option, $oldValue, $newValue ) {
		// No need to process if nothing was updated
		if ( $oldValue === $newValue ) {
			return false;
		}

		$exemptIfEquals = array(
			'active_plugins'    => true,
			'html_type'         => true,
			'fs_accounts'       => true,
			'rewrite_rules'     => true,
			'uninstall_plugins' => true,
			'wp_user_roles'     => true,
		);

		// If we have an exact match, we can just stop here.
		if ( array_key_exists( $option, $exemptIfEquals ) ) {
			return false;
		}

		$forceIfContains = array(
			'html',
			'css',
			'style',
			'query',
			'queries',
		);

		$exemptIfContains = array(
			'_active',
			'_activated',
			'_activation',
			'_attempts',
			'_available',
			'_blacklist',
			'_cache_validator',
			'_check_',
			'_checksum',
			'_config',
			'_count',
			'_dectivated',
			'_disable',
			'_enable',
			'_errors',
			'_hash',
			'_inactive',
			'_installed',
			'_key',
			'_last_',
			'_license',
			'_log_',
			'_mode',
			'_options',
			'_pageviews',
			'_redirects',
			'_rules',
			'_schedule',
			'_session',
			'_settings',
			'_shown',
			'_stats',
			'_status',
			'_statistics',
			'_supports',
			'_sync',
			'_task',
			'_time',
			'_token',
			'_traffic',
			'_transient',
			'_url_',
			'_version',
			'_views',
			'_visits',
			'_whitelist',
			'404s',
			'cron',
			'limit_login_',
			'nonce',
			'user_roles',
		);

		$force_purge = false;

		if ( ctype_upper( str_replace( array( '-', '_' ), '', $option ) ) ) {
			$option = strtolower( $option );
		}
		$option_name = '_' . to_snake_case( to_studly_case( $option ) ) . '_';

		foreach ( $forceIfContains as $slug ) {
			if ( false !== strpos( $option_name, $slug ) ) {
				$force_purge = true;
				break;
			}
		}

		if ( ! $force_purge ) {
			foreach ( $exemptIfContains as $slug ) {
				if ( false !== strpos( $option_name, $slug ) ) {
					return false;
				}
			}
		}

		$this->purge_all();

		return true;
	}

	/**
	 * Checks if a taxonomy is public.
	 *
	 * @param  string $taxonomy  Taxonomy name.
	 *
	 * @return boolean
	 */
	protected function is_public_taxonomy( $taxonomy ) {
		$public          = false;
		$taxonomy_object = get_taxonomy( $taxonomy );
		if ( $taxonomy_object && isset( $taxonomy_object->public ) ) {
			$public = $taxonomy_object->public;
		}

		return $public;
	}
}
