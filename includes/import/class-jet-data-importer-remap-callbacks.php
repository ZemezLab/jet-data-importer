<?php
/**
 * Jimporter post processing callbacks
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Data_Importer_Callbacks' ) ) {

	/**
	 * Define Jet_Data_Importer_Callbacks class
	 */
	class Jet_Data_Importer_Callbacks {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Holder for terms data
		 *
		 * @var array
		 */
		public $terms = array();

		/**
		 * Store processed shortcodes data
		 *
		 * @var array
		 */
		private $shortcodes_data = array();

		/**
		 * Constructor for the class
		 */
		public function __construct() {
			// Manipulations with posts remap array
			add_action( 'jet-data-importer/import/remap-posts', array( $this, 'process_options' ) );
			add_action( 'jet-data-importer/import/remap-posts', array( $this, 'postprocess_posts' ) );
			add_action( 'jet-data-importer/import/remap-posts', array( $this, 'process_thumbs' ) );
			add_action( 'jet-data-importer/import/remap-posts', array( $this, 'process_home_page' ) );

			// Manipulations with terms remap array
			add_action( 'jet-data-importer/import/remap-terms', array( $this, 'process_term_parents' ) );
			add_action( 'jet-data-importer/import/remap-terms', array( $this, 'process_nav_menu' ) );
			add_action( 'jet-data-importer/import/remap-terms', array( $this, 'process_nav_menu_widgets' ) );
			add_action( 'jet-data-importer/import/remap-terms', array( $this, 'process_home_page' ) );

		}

		/**
		 * Remap IDs in home page content
		 *
		 * @param  array $data Mapped terms data.
		 * @return void|false
		 */
		public function process_home_page( $data ) {

			$regex = apply_filters( 'jet-data-importer/import/home-regex-replace', array() );

			if ( empty( $regex ) ) {
				return false;
			}

			$pages = apply_filters( 'jet-data-importer/import/add-pages-to-replace', array() );

			if ( ! empty( $pages ) ) {
				$pages = array_map( array( $this, 'get_page_ids' ), $pages );
			}

			$home_id = get_option( 'page_on_front' );

			$pages = array_merge( array( $home_id ), $pages );
			$pages = array_filter( $pages );

			if ( ! $pages ) {
				return false;
			}

			$regex = array_map( array( $this, 'prepare_regex' ), $regex );

			foreach ( $pages as $page_id ) {

				$page = get_post( $page_id );

				$this->terms = $data;

				$content = preg_replace_callback( $regex, array( $this, 'replace_ids' ), $page->post_content );

				$new_page = array(
					'ID'           => $page_id,
					'post_content' => $content,
				);

				wp_update_post( $new_page );

			}
		}

		/**
		 * Get page ids by slug
		 *
		 * @return int|bool
		 */
		public function get_page_ids( $slug ) {

			$page = get_page_by_path( $slug );
			if ( $page ) {
				return $page->ID;
			} else {
				return false;
			}

			return get_page_by_path( $slug );
		}

		/**
		 * Replace ids in shortcodes
		 *
		 * @return string
		 */
		public function replace_ids( $matches ) {

			if ( 5 !== count( $matches ) ) {
				return $matches[0];
			}

			$tag       = $matches[2];
			$attr      = $matches[3];
			$data      = $this->shortcodes_data;
			$delimiter = isset( $data[ $tag ][ $attr ] ) ? $data[ $tag ][ $attr ] : ',';
			$ids       = explode( $delimiter, $matches[4] );
			$new_ids   = array();

			foreach ( $ids as $id ) {

				if ( isset( $this->terms[ $id ] ) ) {
					$new_ids[] = $this->terms[ $id ];
				} else {
					$new_ids[] = $id;
				}

			}

			$new_ids = implode( $delimiter, $new_ids );
			$return  = sprintf( '%1$s="%2$s"', $matches[1], $new_ids );

			return $return;
		}

		/**
		 * Callback for regex map
		 *
		 * @param  array $item Regex item.
		 * @return string
		 */
		public function prepare_regex( $item ) {

			$delimiter = isset( $item['delimiter'] ) ? $item['delimiter'] : ',';
			$tag       = $item['shortcode'];
			$attr      = $item['attr'];

			if ( ! isset( $this->shortcodes_data[ $tag ] ) ) {
				$this->shortcodes_data[ $tag ] = array();
			}

			$this->shortcodes_data[ $tag ][ $attr ] = $delimiter;

			return '/(\[(' . $item['shortcode'] . ')[^\]]*(' . $item['attr'] . '))="([0-9\,\s]*)"/';

		}

		/**
		 * Set correctly term parents
		 *
		 * @param  array $data Mapped terms data.
		 * @return void|false
		 */
		public function process_term_parents( $data ) {

			$remap_terms         = jdi_cache()->get( 'terms', 'requires_remapping' );
			$processed_term_slug = jdi_cache()->get( 'term_slug', 'mapping' );

			if ( empty( $remap_terms ) ) {
				return false;
			}

			foreach ( $remap_terms as $term_id => $taxonomy ) {

				$parent_slug = get_term_meta( $term_id, '_wxr_import_parent', true );

				if ( ! $parent_slug ) {
					continue;
				}

				$term_mapping_key = $taxonomy . '-' . $parent_slug;

				if ( empty( $processed_term_slug[ $term_mapping_key ] ) ) {
					continue;
				}

				wp_update_term( $term_id, $taxonomy, array(
					'parent' => (int) $processed_term_slug[ $term_mapping_key ],
				) );

			}

		}

		/**
		 * Replace term thumbnails IDs with new ones
		 *
		 * @param  array $data
		 * @return void
		 */
		public function process_thumbs( $data ) {

			global $wpdb;

			$query_term = "
				SELECT term_id, meta_key, meta_value
				FROM $wpdb->termmeta
				WHERE meta_key LIKE '%_thumb'
			";

			$thumb_term = $wpdb->get_results( $query_term, ARRAY_A );

			$query_post = "
				SELECT post_id, meta_key, meta_value
				FROM $wpdb->postmeta
				WHERE meta_key = '_thumbnail_id'
			";

			$thumb_post = $wpdb->get_results( $query_post, ARRAY_A );

			if ( empty( $thumb_term ) ) {
				$thumb_term = array();
			}

			if ( empty( $thumb_post ) ) {
				$thumb_post = array();
			}

			$thumbnails = array_merge( $thumb_term, $thumb_post );

			foreach ( $thumbnails as $thumb_data ) {

				$meta_key = $thumb_data['meta_key'];
				$current  = $thumb_data['meta_value'];

				if ( '_thumbnail_id' === $meta_key ){
					$id   = $thumb_data['post_id'];
					$func = 'update_post_meta';
				} else {
					$id   = $thumb_data['term_id'];
					$func = 'update_term_meta';
				}

				if ( ! empty( $data[ $current ] ) ) {
					call_user_func( $func, $id, $meta_key, $data[ $current ] );
				}

			}

		}

		/**
		 * Post-process posts.
		 *
		 * @param  array $todo Remap data.
		 * @return void
		 */
		public function postprocess_posts( $mapping ) {

			$todo      = jdi_cache()->get( 'posts', 'requires_remapping' );
			$user_slug = jdi_cache()->get( 'user_slug', 'mapping' );
			$url_remap = jdi_cache()->get_group( 'url_remap' );

			foreach ( $todo as $post_id => $_ ) {

				$data          = array();
				$updated_links = '';
				$old_links     = '';
				$post          = get_post( $post_id );

				$parent_id = get_post_meta( $post_id, '_wxr_import_parent', true );

				if ( ! empty( $parent_id ) && isset( $mapping['post'][ $parent_id ] ) ) {
					$data['post_parent'] = $mapping['post'][ $parent_id ];
				}

				$author_slug = get_post_meta( $post_id, '_wxr_import_user_slug', true );
				if ( ! empty( $author_slug ) && isset( $user_slug[ $author_slug ] ) ) {
					$data['post_author'] = $user_slug[ $author_slug ];
				}

				$has_attachments = get_post_meta( $post_id, '_wxr_import_has_attachment_refs', true );

				if ( ! empty( $has_attachments ) ) {

					$content = $post->post_content;

					// Replace all the URLs we've got
					$new_content = str_replace( array_keys( $url_remap ), $url_remap, $content );
					if ( $new_content !== $content ) {
						$data['post_content'] = $new_content;
					}
				}

				if ( in_array( get_post_type( $post_id ), array( 'page', 'post' ) ) ) {

					$old_links     = ! empty( $data['post_content'] ) ? $data['post_content'] : $post->post_content;
					$updated_links = str_replace( jdi_cache()->get( 'home' ), home_url(), $old_links );

					if ( $updated_links !== $old_links ) {
						$data['post_content'] = $updated_links;
					}

				}

				if ( get_post_type( $post_id ) === 'nav_menu_item' ) {
					$this->postprocess_menu_item( $post_id );
				}

				// Do we have updates to make?
				if ( empty( $data ) ) {
					continue;
				}

				// Run the update
				$data['ID'] = $post_id;
				$result     = wp_update_post( $data, true );

				if ( is_wp_error( $result ) ) {
					continue;
				}

				// Clear out our temporary meta keys
				delete_post_meta( $post_id, '_wxr_import_parent' );
				delete_post_meta( $post_id, '_wxr_import_user_slug' );
				delete_post_meta( $post_id, '_wxr_import_has_attachment_refs' );
			}

		}

		/**
		 * Post-process menu items.
		 *
		 * @param  int $post_id Processed post ID
		 * @return void
		 */
		public function postprocess_menu_item( $post_id ) {

			$menu_object_id = get_post_meta( $post_id, '_wxr_import_menu_item', true );

			if ( empty( $menu_object_id ) ) {
				// No processing needed!
				return;
			}

			$processed_term_id = jdi_cache()->get( 'term_id', 'mapping' );
			$processed_posts   = jdi_cache()->get( 'posts', 'mapping' );

			$menu_item_type = get_post_meta( $post_id, '_menu_item_type', true );

			switch ( $menu_item_type ) {
				case 'taxonomy':
					if ( isset( $processed_term_id[ $menu_object_id ] ) ) {
						$menu_object = $processed_term_id[ $menu_object_id ];
					}
					break;

				case 'post_type':
					if ( isset( $processed_posts[ $menu_object_id ] ) ) {
						$menu_object = $processed_posts[ $menu_object_id ];
					}
					break;

				default:
					// Cannot handle this.
					return;
			}

			if ( ! empty( $menu_object ) ) {
				update_post_meta( $post_id, '_menu_item_object_id', wp_slash( $menu_object ) );
			}

			delete_post_meta( $post_id, '_wxr_import_menu_item' );

		}

		/**
		 * Remap page ids in imported options
		 *
		 * @param  array $data Remap data.
		 * @return void
		 */
		public function process_options( $data ) {

			$options_to_process = array(
				'page_on_front',
				'page_for_posts',
			);

			foreach ( $options_to_process as $key ) {

				$current = get_option( $key );

				if ( ! $current || ! isset( $data[ $current ] ) ) {
					continue;
				}

				update_option( $key, $data[ $current ] );

			}

		}

		/**
		 * Remap nav menu ids
		 *
		 * @param  array $data Remap data.
		 * @return void
		 */
		public function process_nav_menu( $data ) {

			$locations = get_nav_menu_locations();

			if ( empty( $locations ) ) {
				return;
			}

			$new_locations = array();

			foreach ( $locations as $location => $id ) {

				if ( isset( $data[ $id ] ) ) {
					$new_locations[ $location ] = $data[ $id ];
				} else {
					$new_locations[ $location ] = $id;
				}

			}

			set_theme_mod( 'nav_menu_locations', $new_locations );

		}

		/**
		 * Remap menu IDs in widgets
		 *
		 * @param  array $data Remap data.
		 * @return void
		 */
		public function process_nav_menu_widgets( $data ) {

			$widget_menus = get_option( 'widget_nav_menu' );

			if ( empty( $widget_menus ) ) {
				return;
			}

			$new_widgets = array();

			foreach ( $widget_menus as $key => $widget ) {

				if ( '_multiwidget' === $key ) {
					$new_widgets['_multiwidget'] = $widget;
					continue;
				}

				if ( empty( $widget['nav_menu'] ) ) {
					$new_widgets[] = $widget;
					continue;
				}

				$id = $widget['nav_menu'];

				if ( isset( $data[ $id ] ) ) {
					$widget['nav_menu'] = $data[ $id ];
				}

				$new_widgets[ $key ] = $widget;

			}

			update_option( 'widget_nav_menu', $new_widgets );

		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}

/**
 * Returns instance of Jet_Data_Importer_Callbacks
 *
 * @return object
 */
function jdi_remap_callbacks() {
	return Jet_Data_Importer_Callbacks::get_instance();
}

jdi_remap_callbacks();
