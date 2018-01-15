<?php

/**
 * Kashing Payment Complete shortcode.
 *
 */

if ( !function_exists( 'kashing_payment_success' ) ) {

    function kashing_payment_success( $atts, $content ) {

        extract( shortcode_atts( array(
            "some_attr" => '',
        ), $atts ) );


        $content = '<br><br>';

        if ( $_GET[ 'kTransactionID' ] ) { // Retrieve parameters from the URL
            $content .= '<br>Transaction ID: ' . esc_html( $_GET[ 'kTransactionID' ] );
        } else {
            return;
        }

        if ( $_GET[ 'kResponse' ] ) {
            $content .= '<br>Response: ' . esc_html( $_GET[ 'kResponse' ] );
        }

        if ( $_GET[ 'kReason' ] ) {
            $content .= '<br>Reason: ' . esc_html( $_GET[ 'kReason' ] );
        }

        ?>

        Hello


        <?php

        return $content; // Return the shortcode content

    }

    add_shortcode( 'kashing_payment_success', 'kashing_payment_success' );

}