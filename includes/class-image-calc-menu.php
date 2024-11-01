<?php

class Wp_Image_Calc_Menu {

    private static $instance;

    public static function initialize() {

        if ( ! isset( self::$instance ) ) {

            self::$instance = new Wp_Image_Calc_Menu();
            self::$instance->init();
        }

        return self::$instance;
    }

    private function init() {

        add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_styles_scripts' ) );
    }

    public function register_styles_scripts() {

        // Load styles and scripts only when required
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wp-img-calculator' ) {

            wp_enqueue_script( 'img-calc-script', IMG_CALC_PLUGIN_URL . 'dist/app.bundle.js', array(), IMG_CALC_VERSION, true );
            wp_localize_script( 'img-calc-script', 'IMG_CALC', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ), 
                'nonce' => wp_create_nonce( 'wp-img-calc-nonce' )
            ));
        }
    }

    public function register_menu_page() {

        add_submenu_page(
	        'upload.php',
	        __('WP Image Calculator', 'img-calc'),
	        __('WP Image Calc.', 'img-calc'),
	        'manage_options',
	        'wp-img-calculator',
	        array($this, 'render_html') 
	    );
    }

    public function render_html() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('WP Image Calculator', 'img-calc') ?></h1>
            <hr class="wp-header-end">
            <div id="wp-image-calc"></div>
        </div>
        <?php
    }
}

Wp_Image_Calc_Menu::initialize();