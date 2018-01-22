<?php

/**
 * Kashing Payment Failure shortcode.
 *
 */

if ( !function_exists( 'kashing_payment_failure' ) ) {

    function kashing_payment_failure( $atts, $content ) {

        $admin_notice = '';

        extract( shortcode_atts( array(
            "admin_notice" => 'yes',
        ), $atts ) );

        $output = '';

        if ( isset( $_GET[ 'kTransactionID' ] ) ) { // Proceed only if parameter is available in $_GET

            $transaction_id = $_GET[ 'kTransactionID' ];
            $kashing_api = new Kashing_API();

            $transaction_details = $kashing_api->api_get_transaction_error_details( $transaction_id );

            // Regular user output

            if ( isset( $transaction_details['gatewaymessage'] ) ) {
                $extra_class = ( isset( $transaction_details['nogateway'] ) ) ? ' no-gateway-message' : '';
                $output = '<div class="kashing-transaction-details kashing-gateway-message' . $extra_class . '"><p>';
                $output .= esc_html( $transaction_details['gatewaymessage'] );
                $output .= '</p></div>';
            }

            // Extra details for admin users

            if ( $admin_notice != 'no' && current_user_can( 'administrator' ) ) {
                $output .= '<div class="kashing-frontend-notice kashing-errors">';
                $output .= '<p><strong>' . __( 'Kashing payment failed.', 'kashing' ) . '</strong></p><p>' . __( 'Transaction details', 'kashing' ) . ':</p><ul>';
                $output .= '<li>' . __( 'Transaction ID', 'kashing' ) . ': <strong>' . esc_html( $_GET[ 'kTransactionID' ] ) . '</strong></li>';
                if ( $_GET[ 'kResponse' ] ) {
                    $output .= '<li>' . __( 'Response Code', 'kashing' ) . ': <strong>' . esc_html( $_GET[ 'kResponse' ] ) . '</strong></li>';
                }
                if ( $_GET[ 'kReason' ] ) {
                    $output .= '<li>' . __( 'Reason Code', 'kashing' ) . ': <strong>' . esc_html( $_GET[ 'kReason' ] ) . '</strong></li>';
                }
                if ( isset( $transaction_details['gatewaymessage'] ) ) {
                    $output .= '<li>' . __( 'Gateway Message', 'kashing' ) . ': <strong>' . esc_html( $transaction_details['gatewaymessage'] ) . '</strong></li>';
                }
                $output .= '</ul><p>' . __( 'This notice is displayed to site administrators only.', 'kashing' ) . '</p>';
                $output .= '</div>';
            }

        }

        return $output; // Return the shortcode content

    }

    add_shortcode( 'kashing_payment_failure', 'kashing_payment_failure' );

}