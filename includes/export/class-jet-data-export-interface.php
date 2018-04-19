<?php
/**
 * Exporter interface
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Data_Export_Interface' ) ) {

	/**
	 * Define Jet_Data_Export_Interface class
	 */
	class Jet_Data_Export_Interface {

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
		function __construct() {

			add_action( 'admin_menu', array( $this, 'menu_page' ) );
			add_action( 'export_filters', array( $this, 'render_export_form' ) );
			add_action( 'wp_ajax_jet-data-export', array( $this, 'run_export' ) );

		}

		/**
		 * Init exporter page
		 *
		 * @return void
		 */
		public function menu_page() {

			jdi()->register_tab(
				array(
					'id'   => 'export',
					'name' => esc_html__( 'Export', 'jet-data-importer' ),
					'cb'   => array( $this, 'render_export_form' ),
				)
			);

		}

		/**
		 * Render export form HTML
		 *
		 * @return void
		 */
		public function render_export_form() {

			ob_start();
			jdi()->get_template( 'export.php' );
			return ob_get_clean();

		}

		/**
		 * Run export process
		 *
		 * @return void
		 */
		public function run_export() {

			if ( ! current_user_can( 'export' ) ) {
				wp_send_json_error( array( 'message' => 'You don\'t have permissions to do this' ) );
			}

			if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'jet-data-export' ) ) {
				wp_send_json_error( array( 'message' => 'You don\'t have permissions to do this' ) );
			}

			require jdi()->path( 'includes/export/class-jet-wxr-exporter.php' );

			$xml = jdi_exporter()->do_export( false );

			$this->download_headers( jdi_exporter()->get_filename() );

			echo $xml;

			die();

		}

		/**
		 * Send download headers
		 *
		 * @return void
		 */
		public function download_headers( $file = 'sample-data.xml' ) {

			session_write_close();

			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Cache-Control: public' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="' . $file . '"' );
			header( 'Content-Transfer-Encoding: binary' );

		}

		/**
		 * Returns URL to generate export file (nonce must be added via JS, otherwise will not be processed)
		 *
		 * @return string
		 */
		public function get_export_url() {
			return add_query_arg( array( 'action' => 'jet-data-export' ), admin_url( 'admin-ajax.php' ) );
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
 * Returns instance of Jet_Data_Export_Interface
 *
 * @return object
 */
function jdi_export_interface() {
	return Jet_Data_Export_Interface::get_instance();
}

jdi_export_interface();
