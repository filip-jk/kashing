<?php

/**
 * Kashing Payment success shortcode. Display some extra details for admin users.
 *
 */

if ( !function_exists( 'kashing_payment_success' ) ) {

    function kashing_payment_success( $atts, $content ) {

        $output = '';

        // Check if this is indeed a return from a gateway.

        if ( $_GET[ 'kTransactionID' ] && current_user_can( 'administrator' ) ) {

            // Display some extra information for an admin user.

            $output .= '<div class="kashing-frontend-notice kashing-success">';
            $output .= '<p><strong>' . __( 'Kashing payment successful!', 'kashing' ) . '</strong></p><p>' . __( 'Transaction details', 'kashing' ) . ':</p><ul>';
            $output .= '<li>' . __( 'Transaction ID', 'kashing' ) . ': <strong>' . esc_html( $_GET[ 'kTransactionID' ] ) . '</strong></li>';
            if ( $_GET[ 'kResponse' ] ) {
                $output .= '<li>' . __( 'Response Code', 'kashing' ) . ': <strong>' . esc_html( $_GET[ 'kResponse' ] ) . '</strong></li>';
            }
            if ( $_GET[ 'kReason' ] ) {
                $output .= '<li>' . __( 'Reason Code', 'kashing' ) . ': <strong>' . esc_html( $_GET[ 'kReason' ] ) . '</strong></li>';
            }
            $output .= '</ul><p>' . __( 'This notice is displayed to site administrators only.', 'kashing' ) . '</p>';
            $output .= '</div>';

        }

        return $output; // Return the shortcode content

    }

    add_shortcode( 'kashing_payment_success', 'kashing_payment_success' );

}