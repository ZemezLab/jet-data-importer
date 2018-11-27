<?php
/**
 * Importer extensions
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Data_Importer_Extensions' ) ) {

	/**
	 * Define Jet_Data_Importer_Extensions class
	 */
	class Jet_Data_Importer_Extensions {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			// Prevent from errors triggering while MotoPress Booking posts importing (loving it)
			add_filter( 'jet-data-importer/import/skip-post', array( $this, 'prevent_import_errors' ), 10, 2 );

			// Clear fonts cache after import
			add_action( 'jet-data-importer/import/finish', array( $this, 'clear_fonts_cache' ) );

			// Allow switch Monstroid2 skins
			add_action( 'jet-data-importer/import/before-skip-redirect', array( $this, 'switch_skin_on_skip' ) );

			add_action( 'jet-data-importer/import/before-options-processing', array( $this, 'set_container_width' ) );
			add_action( 'jet-data-importer/import/after-options-processing', array( $this, 'set_required_options' ) );

			add_action( 'jet-data-importer/import/after-import-tables', array( $this, 'clear_woo_transients' ) );

		}

		/**
		 * Delete WooCommerce-related transients after new tables are imported
		 *
		 * @return void
		 */
		public function clear_woo_transients() {
			delete_transient( 'wc_attribute_taxonomies' );
		}

		/**
		 * Preset elemntor container width if it was not passed in XML
		 */
		public function set_container_width( $data ) {

			if ( ! isset( $data['elementor_container_width'] ) ) {
				update_option( 'elementor_container_width', 1200 );
			}

		}

		/**
		 * Set required Kava Extra and Jet Elements options
		 */
		public function set_required_options() {

			if ( class_exists( 'Kava_Extra' ) ) {

				$options = get_option( 'kava-extra-settings' );

				if ( ! $options ) {
					update_option( 'kava-extra-settings', array(
						'nucleo-mini-package' => 'true',
					) );
				}

				unset( $options );

			}

			if ( class_exists( 'Jet_Elements' ) ) {

				$options = get_option( 'jet-elements-settings' );

				if ( empty( $options ) ) {
					$options = array();
				}

				if ( empty( $options['api_key'] ) ) {
					$options['api_key'] = 'AIzaSyDlhgz2x94h0UZb7kZXOBjwAtszoCRtDLM';
				}

				update_option( 'jet-elements-settings', $options );

			}

		}

		/**
		 * Switch Monstroid2 skin on skip demo content import.
		 *
		 * @return null
		 */
		public function switch_skin_on_skip() {

			if ( ! isset( $_GET['file'] ) ) {
				return;
			}

			preg_match( '/demo-content\/(.*?)\//', base64_decode( $_GET['file'] ), $matches );

			if ( empty( $matches ) || ! isset( $matches[1] ) ) {
				return;
			}

			$skin = esc_attr( $matches[1] );

			$map = array(
				'default' => 'default',
				'skin-1'  => 'skin1',
				'skin-2'  => 'skin2',
				'skin-3'  => 'skin8',
				'skin-4'  => 'skin3',
				'skin-5'  => 'skin4',
				'skin-6'  => 'skin9',
				'skin-7'  => 'skin5',
				'skin-8'  => 'skin7',
				'skin-9'  => 'skin6',
			);

			$mapped_skin = isset( $map[ $skin ] ) ? $map[ $skin ] : false;

			if ( ! $mapped_skin ) {
				return;
			}

			$skin_file = get_stylesheet_directory() . '/tm-style-switcher-pressets/' . $mapped_skin . '.json';

			if ( ! file_exists( $skin_file ) ) {
				return;
			}

			ob_start();
			include $skin_file;
			$skin_data = ob_get_clean();

			$skin_data = json_decode( $skin_data, true );

			if ( empty( $skin_data ) || ! isset( $skin_data['mods'] ) ) {
				return;
			}

			foreach ( $skin_data['mods'] as $mod => $value ) {
				set_theme_mod( $mod, $value );
			}
		}

		/**
		 * Ckear Google fonts cache.
		 *
		 * @return void
		 */
		public function clear_fonts_cache() {
			delete_transient( 'cherry_google_fonts_url' );
			delete_transient( 'cx_google_fonts_url_kava' );
		}

		/**
		 * Prevent PHP errors on import.
		 *
		 * @param  bool   $skip Default skip value.
		 * @param  array  $data Plugin data.
		 * @return bool
		 */
		public function prevent_import_errors( $skip, $data ) {

			if ( isset( $data['post_type'] ) && 'mphb_booking' === $data['post_type'] ) {
				return true;
			}

			return $skip;
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
 * Returns instance of Jet_Data_Importer_Extensions
 *
 * @return object
 */
function jdi_extensions() {
	return Jet_Data_Importer_Extensions::get_instance();
}

jdi_extensions();
