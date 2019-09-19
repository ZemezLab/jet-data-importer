<?php
/**
 * Plugin Name: Jet Data Importer
 * Plugin URI: https://crocoblock.com
 * Description: Import posts, pages, comments, custom fields, categories, tags and more from a WordPress export file.
 * Author: Zemez
 * Author URI:
 * Version: 1.2.2
 * Text Domain: jet-data-importer
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Data_Importer' ) ) {

	/**
	 * Define Jet_Data_Importer class
	 */
	class Jet_Data_Importer {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Holder for importer object instance.
		 *
		 * @var object
		 */
		private $importer = null;

		/**
		 * Holder for importer object instance.
		 *
		 * @var object
		 */
		private $exporter = null;

		/**
		 * Plugin base url
		 *
		 * @var string
		 */
		private $url = null;

		/**
		 * Plugin base path
		 *
		 * @var string
		 */
		private $path = null;

		/**
		 * Items number in single chunk
		 *
		 * @var integer
		 */
		private $chunk_size = 10;

		/**
		 * Registered page tabs
		 * @var array
		 */
		private $page_tabs = array();

		/**
		 * External config
		 *
		 * @var array
		 */
		private $external_config = array();

		/**
		 * External config status
		 *
		 * @var boolean
		 */
		private $has_external = false;

		/**
		 * Menu page slug.
		 * @var string
		 */
		public $slug = 'jet-demo-content';

		/**
		 * Dispalying tab data
		 * @var array
		 */
		public $current_tab = array();

		/**
		 * Constructor for the class
		 */
		function __construct() {

			// Internationalize the text strings used.
			add_action( 'init', array( $this, 'lang' ), -999 );

			add_action( 'init', array( $this, 'start_session' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
			add_filter( 'upload_mimes', array( $this, 'allow_upload_xml' ) );

			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_menu', array( $this, 'menu_page' ), 30 );

			// 1st wizard compat
			add_filter( 'tm_wizard_template_path', array( $this, 'wizard_success_page' ), 10, 2 );
			add_filter( 'tm_wizard_notice_visibility', array( $this, 'wizard_notice_visibility' ) );

			// 2nd wizard compat
			add_filter( 'cherry_plugin_wizard_template_path', array( $this, 'wizard_success_page' ), 10, 2 );
			add_filter( 'cherry_plugin_wizard_notice_visibility', array( $this, 'wizard_notice_visibility' ) );

			// Current wizard compat
			add_filter( 'jet-plugins-wizard/template-path', array( $this, 'wizard_success_page' ), 10, 2 );
			add_filter( 'jet-plugins-wizard/notice-visibility', array( $this, 'wizard_notice_visibility' ) );

		}

		/**
		 * Hide wizard notice on importer pages.
		 *
		 * @param  bool $is_visible Default visibility value.
		 * @return bool
		 */
		public function wizard_notice_visibility( $is_visible ) {

			if ( empty( $_GET['page'] ) || $this->slug !== $_GET['page'] ) {
				return $is_visible;
			}

			return false;
		}

		/**
		 * Chenge wizard success page template
		 *
		 * @param  string $file     Template path.
		 * @param  string $template Template name.
		 * @return string
		 */
		public function wizard_success_page( $file, $template ) {

			if ( 'step-after-install.php' !== $template ) {
				return $file;
			}

			if ( function_exists( 'tm_wizard_interface' ) && is_callable( array( tm_wizard_interface(), 'get_skin_data' ) ) ) {
				return jdi()->path( 'templates/wizard-after-install.php' );
			}

			if ( function_exists( 'cherry_plugin_wizard_interface' ) && is_callable( array( cherry_plugin_wizard_interface(), 'get_skin_data' ) ) ) {
				return jdi()->path( 'templates/wizard-after-install.php' );
			}

			if ( function_exists( 'jet_plugins_wizard_interface' ) && is_callable( array( jet_plugins_wizard_interface(), 'get_skin_data' ) ) ) {
				return jdi()->path( 'templates/wizard-after-install.php' );
			}

			return $file;
		}

		/**
		 * Init plugin
		 *
		 * @return void
		 */
		public function init() {

			$this->set_default_settings();
			$this->set_theme_settings();

			$this->load();
			$this->load_import();
			$this->load_export();

		}

		/**
		 * Loads the translation files.
		 */
		public function lang() {
			load_plugin_textdomain( 'jet-data-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Register menu page
		 */
		public function menu_page() {

			add_menu_page(
				esc_html__( 'Demo Content ', 'jet-data-importer' ),
				esc_html__( 'Demo Content ', 'jet-data-importer' ),
				'manage_options',
				$this->slug,
				array( $this, 'render_plugin_page' ),
				'dashicons-download',
				76
			);

			$this->register_tab(
				array(
					'id'   => 'settings',
					'name' => esc_html__( 'Settings', 'jet-data-importer' ),
					'cb'   => array( $this, 'settings_page' ),
				)
			);

			foreach ( $this->get_page_tabs() as $tab ) {

				if ( empty( $tab['id'] ) || empty( $tab['name'] ) ) {
					continue;
				}

				add_submenu_page(
					$this->slug,
					esc_html__( 'Demo Content ', 'jet-data-importer' ),
					$tab['name'],
					'manage_options',
					sprintf( '%1$s&tab=%2$s', $this->slug, $tab['id'] ),
					array( $this, 'render_plugin_page' )
				);

			}

			remove_submenu_page( $this->slug, $this->slug );

		}

		/**
		 * Render settings page
		 *
		 * @return void
		 */
		public function settings_page() {

			if ( isset( $_POST['jdi_cache_handler'] ) ) {
				update_option( 'jdi_cache_handler', esc_attr( $_POST['jdi_cache_handler'] ), false );
			}

			ob_start();
			$this->get_template( 'page-settings.php' );
			return ob_get_clean();
		}

		/**
		 * Render plugin page html
		 */
		public function render_plugin_page() {

			$this->get_template( 'page-header.php' );

			$tabs = jdi()->get_page_tabs();

			if ( ! $tabs ) {
				return;
			}

			$menu           = '';
			$content        = '';
			$menu_format    = '<li class="cdi-tabs__item"><a class="cdi-tabs__link%3$s" href="%1$s">%2$s</a></li>';
			$content_format = '<div class="cdi-tabs_tab">%1$s</div>';

			if ( empty( $_GET['tab'] ) ) {
				$this->current_tab = $tabs[0];
				$current_tab_id    = $this->current_tab['id'];
			} else {
				$current_tab_id = esc_attr( $_GET['tab'] );
			}

			foreach ( $tabs as $tab ) {

				$current = '';

				if ( $current_tab_id === $tab['id'] ) {

					$current           = ' current-tab';
					$this->current_tab = $tab;

					if ( is_callable( $tab['cb'] ) ) {
						$content = sprintf( $content_format, call_user_func( $tab['cb'] ) );
					}
				}

				$menu .= sprintf( $menu_format, $this->get_tab_link( $tab['id'] ), $tab['name'], $current );
			}

			if ( apply_filters( 'jet-data-importer/tabs-menu-visibility', true ) ) {
				printf( '<ul class="cdi-tabs__menu">%s</ul>', $menu );
			}

			printf( '<div class="cdi-tabs__content">%s</div>', $content );

			$this->get_template( 'page-footer.php' );
		}

		/**
		 * Return page tabs array
		 *
		 * @return array
		 */
		public function get_page_tabs() {
			return $this->page_tabs;
		}

		/**
		 * Returns current tab URL
		 *
		 * @param  string $tab Current tab ID.
		 * @return string
		 */
		public function get_tab_link( $tab = '' ) {

			return add_query_arg(
				array(
					'page' => $this->slug,
					'tab'  => $tab,
				),
				esc_url( admin_url( 'admin.php' ) )
			);
		}

		/**
		 * Register new tab for plugin page.
		 *
		 * @param  array $tab Tab data to registrate.
		 * @return void
		 */
		public function register_tab( $tab = array() ) {

			$tab = wp_parse_args( $tab, array(
				'id'   => 'tab',
				'name' => null,
				'cb'   => null,
			) );

			$this->page_tabs[] = $tab;

		}

		/**
		 * Add XML to alowed MIME types to upload
		 *
		 * @param  array $mimes Allowed MIME-types.
		 * @return array
		 */
		public function allow_upload_xml( $mimes ) {
			$mimes = array_merge( $mimes, array( 'xml' => 'application/xml' ) );
			return $mimes;
		}

		/**
		 * Run session
		 *
		 * @return void
		 */
		public function start_session() {

			if ( ! session_id() ) {
				session_start();
			}

		}

		/**
		 * Get plugin template
		 *
		 * @param  string $template Template name.
		 * @return void
		 */
		public function get_template( $template ) {

			$file = locate_template( 'jet-data-importer/' . $template );

			if ( ! $file ) {
				$file = $this->path( 'templates/' . $template );
			}

			if ( file_exists( $file ) ) {
				include $file;
			}

		}

		/**
		 * Load globally required files
		 */
		public function load() {

			require $this->path( 'includes/class-jet-data-importer-cache.php' );
			require $this->path( 'includes/class-jet-data-importer-logger.php' );
			require $this->path( 'includes/class-jet-data-importer-tools.php' );
			require $this->path( 'includes/class-jet-data-importer-slider.php' );
			require $this->path( 'includes/class-jet-data-importer-files-manager.php' );
		}

		/**
		 * Set default importer settings
		 *
		 * @return void
		 */
		public function set_default_settings() {

			include $this->path( 'includes/config/default-config.php' );

			/**
			 * @var array $settings defined in manifest file
			 */
			$this->settings = apply_filters( 'jet-data-importer/default-config', $config );

		}

		public function add_external_config( $config = array() ) {
			$this->has_external    = true;
			$this->external_config = array_merge( $this->external_config, $config );
		}

		/**
		 * Maybe rewrite settings from active theme
		 *
		 * @return void
		 */
		public function set_theme_settings() {

			if ( empty( $this->external_config ) ) {
				$this->maybe_get_remote_skins();
				return;
			}

			$allowed_settings = array_keys( $this->settings );

			/**
			 * List of settings that should be rewritten from 3rd party source instead of merging
			 */
			$rewrite = apply_filters( 'jet-data-importer/settings/rewrite-keys', array(
				'success-links',
				'advanced_import',
			) );

			foreach ( $allowed_settings as $type ) {
				if ( isset( $this->external_config[ $type ] ) ) {

					if ( in_array( $type, $rewrite ) ) {
						$this->settings[ $type ] = $this->external_config[ $type ];
					} else {
						$this->settings[ $type ] = wp_parse_args(
							$this->external_config[ $type ],
							$this->settings[ $type ]
						);
					}

					if ( 'advanced_import' === $type && isset( $this->settings[ $type ]['from_path'] ) ) {
						$this->settings[ $type ] = $this->get_remote_skins( $this->settings[ $type ]['from_path'] );
					}

				}
			}

		}

		/**
		 * If 3-rd party config not passed - check maybe we need to get default remote config.
		 *
		 * @return [type] [description]
		 */
		public function maybe_get_remote_skins() {

			if ( ! isset( $this->settings['advanced_import'] ) ) {
				return;
			}

			if ( ! isset( $this->settings['advanced_import']['from_path'] ) ) {
				return;
			}

			$this->settings['advanced_import'] = $this->get_remote_skins(
				$this->settings['advanced_import']['from_path']
			);

		}

		/**
		 * Get remote skins if config provides URL for scins API
		 *
		 * @param  string $url API URL to get skins from.
		 * @return array
		 */
		public function get_remote_skins( $url ) {

			$transient = 'jet_wizard_skins';
			$data      = get_site_transient( $transient );

			if ( $this->has_external ) {
				$data = false;
			}

			if ( ! $data ) {

				$response = wp_remote_get( $url, array(
					'timeout'   => 60,
					'sslverify' => false,
				) );

				$data = wp_remote_retrieve_body( $response );
				$data = json_decode( $data, true );

				if ( empty( $data ) ) {
					$data = array();
				}

				if ( ! $this->has_external ) {
					set_site_transient( $transient, $data, 2 * DAY_IN_SECONDS );
				}

			}

			$result = array();

			if ( ! isset( $data['advanced'] ) ) {
				return $result;
			}

			foreach ( $data['advanced'] as $slug => $skin ) {
				$result[ $slug ] = array(
					'label'    => $skin['name'],
					'full'     => $skin['full_xml'],
					'lite'     => $skin['lite_xml'],
					'thumb'    => $skin['thumb'],
					'demo_url' => $skin['demo'],
				);
			}

			return $result;

		}

		/**
		 * Get setting by name
		 *
		 * @param  array $keys Settings key to get.
		 * @return void
		 */
		public function get_setting( $keys = array() ) {

			if ( empty( $keys ) || ! is_array( $keys ) ) {
				return false;
			}

			$temp_result = $this->settings;

			foreach ( $keys as $key ) {

				if ( ! isset( $temp_result[ $key ] ) ) {
					continue;
				}

				$temp_result = $temp_result[ $key ];
			}

			return $temp_result;

		}

		/**
		 * Include import files
		 */
		public function load_import() {

			$this->load_wp_importer();
			require $this->path( 'includes/import/class-jet-data-importer-interface.php' );

			jdi_interface();
		}

		/**
		 * Load export-related files
		 *
		 * @return void
		 */
		public function load_export() {

			if ( ! is_admin() ) {
				return;
			}

			require $this->path( 'includes/export/class-jet-data-export-interface.php' );

		}

		/**
		 * Returns path to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function path( $path = null ) {

			if ( ! $this->path ) {
				$this->path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->path . $path;

		}

		/**
		 * Returns url to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function url( $path = null ) {

			if ( ! $this->url ) {
				$this->url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->url . $path;

		}

		/**
		 * Prepare assets URL depending from CHERRY_DEBUG value
		 *
		 * @param  string $path Base file path.
		 * @return string
		 */
		public function assets_url( $path ) {
			return $this->url( 'assets/' . $path );
		}

		/**
		 * Register plugin script and styles
		 *
		 * @return void
		 */
		public function register_assets() {

			wp_register_style(
				'jet-data-import',
				$this->assets_url( 'css/jet-data-import.css' ),
				array(),
				'1.2.1'
			);

			wp_register_script(
				'jet-data-import',
				$this->assets_url( 'js/jet-data-import.js' ),
				array(),
				'1.2.1',
				true
			);

			wp_register_script(
				'jet-data-export',
				$this->assets_url( 'js/jet-data-export.js' ),
				array(),
				'1.2.1',
				true
			);

			wp_localize_script( 'jet-data-import', 'JetDataImportVars', array(
				'nonce'        => wp_create_nonce( 'jet-data-import' ),
				'autorun'      => $this->import_autorun(),
				'uploadTitle'  => esc_html__( 'Select or upload file with demo content', 'jet-data-importer' ),
				'uploadBtn'    => esc_html__( 'Select', 'jet-data-importer' ),
				'file'         => ( isset( $_REQUEST['file'] ) ) ? esc_attr( $_REQUEST['file'] ) : false,
				'skin'         => ( isset( $_REQUEST['skin'] ) ) ? esc_attr( $_REQUEST['skin'] ) : 'default',
				'xml_type'     => ( isset( $_REQUEST['xml_type'] ) ) ? esc_attr( $_REQUEST['xml_type'] ) : 'lite',
				'tab'          => jdi_interface()->slug,
				'error'        => esc_html__( 'Data processing error, please try again!', 'jet-data-importer' ),
				'advURLMask'   => $this->page_url( array( 'tab' => 'import', 'step' => 2, 'file' => '<-file->' ) ),
			) );

			wp_localize_script( 'jet-data-export', 'JetDataExportVars', array(
				'nonce'       => wp_create_nonce( 'jet-data-export' ),
			) );

		}

		/**
		 * Generate import page URL.
		 *
		 * @param  array  $args Arguments array.
		 * @return string
		 */
		public function page_url( $args = array() ) {

			$default = array(
				'page' => $this->slug,
			);

			if ( ! empty( $_REQUEST['referrer'] ) ) {
				$default['referrer'] = esc_attr( $_REQUEST['referrer'] );
			}

			if ( empty( $default['referrer'] ) && $this->get_server_ref() ) {
				$default['referrer'] = $this->get_server_ref();
			}

			$args = array_merge( $default, $args );

			return add_query_arg( $args, esc_url( admin_url( 'admin.php' ) ) );
		}

		/**
		 * Try to get referrer from server vars
		 *
		 * @return string|bool false
		 */
		public function get_server_ref() {

			if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
				return false;
			}

			$parts = parse_url( $_SERVER['HTTP_REFERER'] );

			if ( ! $parts || empty( $parts['query'] ) ) {
				return false;
			}

			parse_str( $parts['query'], $query );

			if ( empty( $query ) || empty( $query['referrer'] ) ) {
				return false;
			}

			return esc_attr( $query['referrer'] );
		}

		/**
		 * Check if import autorun is allowed.
		 *
		 * @return boolean
		 */
		public function import_autorun() {

			if ( isset( $_GET['type'] ) && 'replace' === $_GET['type'] ) {
				return false;
			}

			if ( isset( $_GET['file'] ) ) {
				return esc_attr( $_GET['file'] );
			} else {
				return false;
			}

		}

		/**
		 * Enqueue globally required assets
		 *
		 * @return void
		 */
		public function enqueue_assets( $hook ) {

			if ( isset( $_GET['page'] ) && $this->slug === $_GET['page'] ) {
				wp_enqueue_style( 'jet-data-import' );
				wp_enqueue_media();
			}

		}

		/**
		 * Loads default WordPress importer
		 *
		 * @return void
		 */
		public function load_wp_importer() {

			if ( ! class_exists( 'WP_Importer' ) ) {
				require ABSPATH . '/wp-admin/includes/class-wp-importer.php';
			}

		}

		/**
		 * Return importer instance.
		 *
		 * @return object
		 */
		public function importer() {
			return $this->importer;
		}

		/**
		 * Return exporter instance
		 *
		 * @return object
		 */
		public function exporter() {
			return $this->exporter;
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
 * Returns instance of Jet_Data_Importer
 *
 * @return object
 */
function jdi() {
	return Jet_Data_Importer::get_instance();
}

jdi();

/**
 * Register configuration from 3rd party theme or plugin.
 * Should be called on 'init' hook with priority 9 or earlier.
 *
 * @param  array $config Array of settings to register.
 * @return void
 */
function jet_data_importer_register_config( $config = array() ) {
	jdi()->add_external_config( $config );
}
