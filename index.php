<?php

/*

Plugin Name: 	Kashing
Plugin URI: 	ttp://themeforest.net/user/Veented
Description: 	Easily integrate Kashing Payment with your WordPress website.
Version: 		1.0
Author: 		Veented
Author URI: 	http://themeforest.net/user/Veented
License: 		GPL2

*/

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'KASHING_PATH', dirname(__FILE__) . '/' );

class Kashing_Payments {

    public static $data_prefix = 'ksng-'; // Taki prefix uzywamy do nazw funkcji

    /**
     * Class constructor.
     */

    function __construct() {

        // Load Metabox Core

        $this->load_metaboxes();

        // Plugin Options Page

        $this->settings_page();

        // Plugin scripts and styles

        add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );

        // Register custom post types

        $this->register_post_type();

        // Load Metaboxes

        $this->load_metaboxes();

        // Load Shortcodes

        $this->load_shortcodes();

        // Init Ajax

        $this->plugin_ajax();

        // Kashing Functions

        require_once KASHING_PATH . 'inc/kashing-functions.php';
        require_once KASHING_PATH . 'inc/helpers/kashing-currency.php';

    }

    /**
     * Load Metaboxes.
     */

    private function load_metaboxes() {

        require_once KASHING_PATH . 'inc/metaboxes.php';

    }

    /**
     * Plugin Options Page.
     */

    private function settings_page() {

        require_once KASHING_PATH . 'inc/kashing-settings.php';

    }

    /**
     * Register the main Kashing custom post type.
     */

    private function register_post_type() {

        add_action( 'init', array( $this, 'action_register_post_type_kashing' ) );

    }

    /**
     * Custom post type registration action.
     */

    public function action_register_post_type_kashing() { // Must be public so it can be accessed by WordPress add_action()

        $args = array(
            'label' => __( 'Kashing', 'kashing' ),
            'public' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'hierarchical' => true,
            'has_archive' => false,
            'menu_icon' => 'dashicons-tickets-alt',
            'supports' => array( 'title' ),
            'exclude_from_search' => true,
            'show_in_nav_menus' => false,
            'show_in_menu' => true,
            'show_in_admin_bar' => false,
            'has_archive' => false,
            'public' => false,
            'publicly_queryable' => true,
            'rewrite' => false,
            'labels' => array(
                'add_new' => __( 'Add New Form', 'kashing' ),
                'all_items' => __( 'View Forms', 'kashing' )
            )
        );

        register_post_type( 'kashing' , $args );

    }

    /**
     * Admin scripts and styles.
     */

    public function action_admin_enqueue_scripts() {

        wp_enqueue_style( 'kashing-admin', plugin_dir_url( __FILE__ ) . 'assets/css/kashing-admin.css' );
        wp_enqueue_script( 'kashing-backend-js', plugin_dir_url( __FILE__ ) . 'assets/js/kashing-backend.js', array( 'jquery' ) );
	    wp_enqueue_script( 'kashing-metaboxes-js', plugin_dir_url( __FILE__ ) . 'assets/js/kashing-metaboxes.js', array( 'jquery' ) );

        wp_localize_script(
            'kashing-backend-js',
            'post_types_wp_object',
            array(
                'wp_ajax_url' => admin_url( 'admin-ajax.php' )
            )
        );

    }

    /**
     * Frontend scripts and styles.
     */

    public function action_wp_enqueue_scripts() {

        wp_enqueue_style( 'kashing-frontend-css', plugin_dir_url( __FILE__ ) . 'assets/css/kashing-frontend.css' );
        
        wp_enqueue_script( 'kashing-frontend-js', plugin_dir_url( __FILE__ ) . 'assets/js/kashing-frontend.js', array( 'jquery' ) );

        // Localize the frontend JavaScript

        wp_localize_script(
            'kashing-frontend-js',
            'kashing_wp_object',
            array(
                'wp_ajax_url' => admin_url( 'admin-ajax.php' )
            )
        );

    }

    /**
     * Load shortcodes.
     */

    private function load_shortcodes() {


        require_once KASHING_PATH . 'inc/shortcodes/kashing-form.php';
        require_once KASHING_PATH . 'inc/shortcodes/payment-complete.php';

        // "Add Kashing Form" button

        add_action( 'media_buttons', array( $this, 'action_add_shortcode_button' ) ); // Dodaje przycisk NAD edytor

    }

    /**
     * Add a shortcode button to the WordPress post/page editor.
     */

    public function action_add_shortcode_button( $editor_id ) {
        echo '<a href="#" class="button" id="add-kashing-form">' . esc_html__( 'Add Kashing Form', 'kashing' ) . '</a>';
    }

    /**
     * Plugin ajax related.
     */

    public function plugin_ajax() {

        require_once KASHING_PATH . 'inc/kashing-ajax.php';

    }

}

$kashing_payments = new Kashing_Payments();