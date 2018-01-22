<?php

class Kashing_Shortcodes {

    /**
     * Constructor class
     */

    function __construct() {

        // Include shortcodes

        require_once KASHING_PATH . 'inc/shortcodes/kashing-form.php';
        require_once KASHING_PATH . 'inc/shortcodes/payment-success.php';
        require_once KASHING_PATH . 'inc/shortcodes/payment-failure.php';

        // "Add Kashing Form" button

        add_action( 'media_buttons', array( $this, 'action_add_shortcode_button' ) );

        // Add am ajax call function to retrieve Kashing Forms

        add_action( 'wp_ajax_call_get_kashing_forms',  array( $this, 'ajax_get_kashing_forms' )  );
        add_action( 'wp_ajax_nopriv_call_get_kashing_forms', array( $this, 'ajax_get_kashing_forms' )  );
    }

    /**
     * Add a shortcode button to the WordPress post/page editor.
     *
     * @return void
     */

    public function action_add_shortcode_button( $editor_id ) {
        echo '<a href="#" class="button" id="add-kashing-form">' . esc_html__( 'Add Kashing Form', 'kashing' ) . '</a>';
    }

    /**
     * Get Kashing Form posts AJAX function.
     *
     * * @return void
     */

    function ajax_get_kashing_forms() {

        $custom_post_data = array();

        $args = array(
            'post_type' => 'kashing',
            'posts_per_page' => -1
        );

        $the_query = new WP_Query( $args );

        if ($the_query -> have_posts() ) {
            while ( $the_query->have_posts() ) {
                $the_query -> the_post();

                $title = get_the_title();
                $id = get_the_ID();

                array_push( $custom_post_data,
                    array(
                        'text' => $title,
                        'value' => strval($id)
                    ) );
            }

            $response = array(
                'data' => array_reverse( $custom_post_data )
            );

            wp_send_json_success( json_encode( $response ) );
        } else {
            wp_send_json_error( __( 'No kashing forms created.', 'kashing' ) );
        }

        wp_reset_query();
    }


}

$kashing_shortcodes = new Kashing_Shortcodes();