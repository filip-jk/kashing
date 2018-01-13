<?php

if ( !function_exists( 'kashing_payment_complete_shortcode' ) ) {

    function kashing_payment_complete_shortcode( $atts, $content ) {

        extract( shortcode_atts( array(
            "some_attr" => '',
        ), $atts ) );

        $content = '<br><br>';

        if ( $_GET[ 'TransactionID' ] ) { // Retrieve parameters from the URL
            $content .= '<br>Transaction ID: ' . esc_html( $_GET[ 'TransactionID' ] );
        } else {
            return;
        }

        if ( $_GET[ 'Response' ] ) {
            $content .= '<br>Response: ' . esc_html( $_GET[ 'Response' ] );
        }

        if ( $_GET[ 'Reason' ] ) {
            $content .= '<br>Reason: ' . esc_html( $_GET[ 'Reason' ] );
        }

        ?>

        Hello


        <?php

        return $content; // Return the shortcode content

    }

    add_shortcode( 'kashing_payment_complete', 'kashing_payment_complete_shortcode' );

}

function hook_css() {

        if ( array_key_exists( 'Response', $_GET ) ) {
            wp_redirect( 'http://google.com' );
            exit;
        }


}

add_action( 'wp_loaded', 'hook_css' );