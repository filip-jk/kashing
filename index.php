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

/**
 * Main plugin class.
 */

class Kashing_Payments {

    /**
     * The general plugin prefix.
     *
     * @var string
     */

    public static $data_prefix = 'ksng-'; // Taki prefix uzywamy do nazw funkcji

    /**
     * Class constructor.
     */

    function __construct() {

        // Kashing Functions
        require_once KASHING_PATH . 'inc/kashing-functions.php';

        // Plugin Setup Related
        $this->plugin_setup();

        // Load Metaboxes
        require_once KASHING_PATH . 'inc/class.kashing-metaboxes.php';

        // Plugin scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );

        // Custom post type
        require_once KASHING_PATH . 'inc/class.kashing-post-type.php';

        // Load Shortcodes
        require_once KASHING_PATH . 'inc/class.kashing-shortcodes.php';

        // Init Ajax
        require_once KASHING_PATH . 'inc/class.kashing-api.php';

        // Helpers
        require_once KASHING_PATH . 'inc/helpers/currency/class.kashing-currency.php'; // Currency
        require_once KASHING_PATH . 'inc/helpers/countries/class.kashing-countries.php'; // Countries

        // Kashing Form Fields
        require_once KASHING_PATH . 'inc/class.kashing-fields.php';

        // Plugin Options Page
        require_once KASHING_PATH . 'inc/class.kashing-settings.php';

    }

    /**
     * General plugin setup.
     */

    public function plugin_setup() {

        // Plugin activation hook
        register_activation_hook( __FILE__, array( $this, 'plugin_activation_hook' ) );

        // Plugin additional links
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__ ), array( $this, 'plugin_action_links' ) );

        // Custom page states (failure and success pages)
        add_filter( 'display_post_states', array( $this, 'custom_page_states' ), 10, 2 );

    }

    /**
     * Plugin additional action links.
     *
     * @param array
     *
     * @return array
     */

    public function plugin_action_links( $links ) {
        $links[] = '<a href="'. esc_url( get_admin_url(null, 'edit.php?post_type=kashing&page=kashing-settings') ) .'">' . esc_html__( 'Settings', 'kashing' ) . '</a>';
        return $links;
    }

    /**
     * Registering custom post states for pages (Failure and Success pages).
     *
     * @param array
     * @param int
     *
     * @return array
     */

    public function custom_page_states( $states, $post ) {

        if ( $post->ID == kashing_option( 'success_page' ) && 'page' == get_post_type( $post->ID ) ) {
            $states[] = __( 'Payment Success', 'kashing' );
        } elseif ( $post->ID == kashing_option( 'failure_page' ) && 'page' == get_post_type( $post->ID ) ) {
            $states[] = __( 'Payment Failure', 'kashing' );
        }

        return $states;
    }

    /**
     * Admin scripts and styles.
     */

    public function action_admin_enqueue_scripts() {

        wp_enqueue_style( 'kashing-admin', plugin_dir_url( __FILE__ ) . 'assets/css/kashing-admin.css' );
        wp_enqueue_script( 'kashing-backend-js', plugin_dir_url( __FILE__ ) . 'assets/js/kashing-backend.js', array( 'jquery' ) );

        // Localize backend scripts

        wp_localize_script(
            'kashing-backend-js',
            'kashing_wp_object',
            array(
                'wp_ajax_url' => admin_url( 'admin-ajax.php' ),
                'msg_missing_field' => __( 'This field is required.', 'kashing' )
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
                'wp_ajax_url' => admin_url( 'admin-ajax.php' ),
                'page_id' => get_the_ID(),
                'msg_missing_field' => __( 'This field is required.', 'kashing' ),
                'msg_invalid_email' => __( 'Please provide a valid e-mail address.', 'kashing' )
            )
        );

    }

    /**
     * Plugin activation hook. Runs whenever the plugin is being activated.
     */

    public function plugin_activation_hook() {

        // Plugin Options Page
        require_once KASHING_PATH . 'inc/class.kashing-settings.php';

        // Check if payment success page is set, if not, create a new one (either if a page with this title is

        if ( kashing_option( 'success_page' ) == null || get_post_status( kashing_option( 'success_page' ) ) === false ) {
            $page_args = array(
                'post_title' => __( 'Payment Success', 'kashing' ),
                'post_content' => 'Example content',
                'post_status' => 'publish',
                'post_type' => 'page'
            );
            $new_page_id = wp_insert_post( $page_args );
            if ( $new_page_id != 0 ) {
                kashing_update_option( 'success_page', $new_page_id ); // Update the Plugin Settings option
            }
        }

        // Same for the payment failure page

        if ( kashing_option( 'failure_page' ) == null || get_post_status( kashing_option( 'failure_page' ) ) === false ) {
            $page_args = array(
                'post_title' => __( 'Payment Failure', 'kashing' ),
                'post_content' => 'Example content',
                'post_status' => 'publish',
                'post_type' => 'page'
            );
            $new_page_id = wp_insert_post( $page_args );
            if ( $new_page_id != 0 ) {
                kashing_update_option( 'failure_page', $new_page_id ); // Update the Plugin Settings option
            }
        }

        // Just in case, set default plugin setting values (Currency for now)

        if ( ! kashing_option( 'currency' ) ) {
            kashing_update_option( 'currency', 'GBP' );
        }

    }

}

$kashing_payments = new Kashing_Payments();