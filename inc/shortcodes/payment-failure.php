<?php

/**
 * Kashing Payment Failure shortcode.
 *
 */

if ( !function_exists( 'kashing_payment_failure' ) ) {

    function kashing_payment_failure( $atts, $content ) {

        extract( shortcode_atts( array(
            "some_attr" => '',
        ), $atts ) );

        $kashing_api = new Kashing_API();

        if ( isset( $_GET ) && array_key_exists( 'kTransactionID' , $_GET ) ) {
            $transaction_id = $_GET[ 'kTransactionID' ];
        }

        //$transaction_id = 'pZ68tUjWmcW';

        ob_start(); // We can use HTML directly thanks to this

        $kashing_api->api_get_transaction_error_details( $transaction_id );

        echo '<pre>';
        var_dump( $kashing_api->api_get_transaction_error_details( $transaction_id ) );
        echo '</pre>';

//        if ( $_GET[ 'TransactionID' ] ) { // Retrieve parameters from the URL
//            $content .= '<br>Transaction ID: ' . esc_html( $_GET[ 'TransactionID' ] );
//        } else {
//            return;
//        }
//
//        if ( $_GET[ 'Response' ] ) {
//            $content .= '<br>Response: ' . esc_html( $_GET[ 'Response' ] );
//        }
//
//        if ( $_GET[ 'Reason' ] ) {
//            $content .= '<br>Reason: ' . esc_html( $_GET[ 'Reason' ] );
//        }

        // TODO: Tutaj trzeba dodac obsluge tego nowego zapytania do API po wiecej info o bledzie.

        $content = ob_get_contents(); // End content "capture" and store it into a variable.
        ob_end_clean();

        return $content; // Return the shortcode content

    }

    add_shortcode( 'kashing_payment_failure', 'kashing_payment_failure' );

}