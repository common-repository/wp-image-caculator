<?php
/**
 * Plugin Name:       WP Image Caculator
 * Plugin URI:        https://www.wprepairgigs.com/plugins/wp-image-calculator
 * Description:       Caculate All the images stored in uploads directory
 * Version:           1.0.0
 * Author:            Support
 * Author URI:        https://www.wprepairgigs.com/
 * Text Domain:       img-calc
 * 
 * WP Image Calculator is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * WP Image Calculator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists('Wp_Image_Calc') ) {
	final class Wp_Image_Calc {

		private static $instance;

		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Wp_Image_Calc ) ) {
				self::$instance = new Wp_Image_Calc();
				self::$instance->constants();
				self::$instance->init();
				self::$instance->includes();
			}

			return self::$instance;
		}

		private function constants() {
			$this->define( 'IMG_CALC_DEBUG', false );
			$this->define( 'IMG_CALC_VERSION', '1.0.0' );
			$this->define( 'IMG_CALC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			$this->define( 'IMG_CALC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		private function init() {

			require IMG_CALC_PLUGIN_DIR . 'vendor/autoload.php';
		}

		private function includes() {

			require_once IMG_CALC_PLUGIN_DIR . 'includes/class-image-calc-process.php';
			require_once IMG_CALC_PLUGIN_DIR . 'includes/class-image-calc-menu.php';
		}

		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}
	}
}


function run_img_calc() {
	return Wp_Image_Calc::get_instance();
}

run_img_calc();