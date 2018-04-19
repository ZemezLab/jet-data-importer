<?php
/**
 * Import page slider
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Data_Importer_Slider' ) ) {

	/**
	 * Define Jet_Data_Importer_Slider class
	 */
	class Jet_Data_Importer_Slider {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Slides list
		 *
		 * @var array
		 */
		private $slides = null;

		/**
		 * Slider data
		 *
		 * @var array
		 */
		private $data = null;

		/**
		 * Constructor for the class
		 */
		function __construct() {

			$slider_data = jdi()->get_setting( array( 'slider' ) );

			if ( empty( $slider_data ) || empty( $slider_data['path'] ) ) {
				return;
			}

			$this->data = $slider_data;

			if ( ! $this->get_slides() ) {
				return;
			}

		}

		/**
		 * Enqueue slider assets
		 *
		 * @return void
		 */
		public function slider_assets() {

			wp_enqueue_script(
				'swiper-jquery',
				jdi()->url( 'assets/js/swiper.min.js' ),
				array( 'jquery' ),
				'2.0.0',
				true
			);

		}

		/**
		 * Render slider
		 *
		 * @return string|void
		 */
		public function render( $echo = true ) {

			$slides = $this->get_slides();

			if ( empty( $slides ) ) {
				return;
			}

			$format = '<div class="swiper-slide">
				<div class="slider-content">
					<img src="%1$s" alt="" data-swiper-parallax="-100">
					<h4 class="slider-title" data-swiper-parallax="-400">%2$s</h4>
					<div class="slider-desc" data-swiper-parallax="-900">%3$s</div>
				</div>
			</div>';

			$result = '';

			foreach ( $slides as $slide ) {

				$url   = ! empty( $slide['image'] ) ? esc_url( $slide['image'] ) : false;
				$title = ! empty( $slide['title'] ) ? wp_kses_post( $slide['title'] ) : false;
				$desc  = ! empty( $slide['desc'] ) ? wp_kses_post( $slide['desc'] ) : false;

				$result .= sprintf( $format, $url, $title, $desc );
			}

			$result = sprintf(
				'<div class="cdi-slider">
					<div class="swiper-container">
						<div class="swiper-wrapper">%1$s</div>
					</div>
					%2$s
				</div>',
				$result,
				'<div class="slider-pagination"></div>'
			);

			if ( $echo ) {
				echo $result;
			} else {
				return $result;
			}

		}

		/**
		 * Retrieve slides list
		 *
		 * @return array|bool false
		 */
		public function get_slides() {

			if ( ! empty( $this->slides ) ) {
				return $this->slides;
			}

			$slides = get_transient( 'jet_data_importer_slides' );

			if ( ! $slides ) {

				$response = wp_remote_get( $this->data['path'], array( 'timeout' => 30 ) );

				if ( ! $response || is_wp_error( $response ) ) {
					return false;
				}

				$body = wp_remote_retrieve_body( $response );

				if ( ! $body || is_wp_error( $body ) ) {
					return false;
				}

				$slides = json_decode( $body, true );

				if ( ! $slides ) {
					return false;
				}

			}

			$this->slides = $slides;
			set_transient( 'jet_data_importer_slides', $this->slides, DAY_IN_SECONDS );

			return $this->slides;
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
 * Returns instance of Jet_Data_Importer_Slider
 *
 * @return object
 */
function jdi_slider() {
	return Jet_Data_Importer_Slider::get_instance();
}
