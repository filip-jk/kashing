<?php

/**
 * Helper function to easily grab a plugin option from the options array.
 *
 * @return string
 */

/**
 * Helper function to easily grab a plugin option from the options array.
 *
 * @param string
 *
 * @return string
 */

if ( !function_exists( 'kashing_option' ) ) {
    function kashing_option( $option_name ) {

        $options = get_option( 'kashing' );

        if ( is_array( $options ) && array_key_exists( $option_name, $options ) ) {
            return $options[ $option_name ]; // Each option has a prefix
        }

        return null;

    }
}

/**
 * Helper function to programatically update plugin settings.
 *
 * @param string
 * @param string
 */

if ( !function_exists( 'kashing_update_option' ) ) {
    function kashing_update_option( $option_name, $new_value ) {

        $options = get_option( 'kashing' );

        if ( is_array( $options ) ) {
            $options[ $option_name ] = esc_attr( $new_value ); // Each option has a prefix
            update_option( 'kashing', $options );
        }

    }
}

/**
 * Grab the currently set currency.
 *
 * @return string
 */

if ( !function_exists( 'kashing_get_currency' ) ) {
    function kashing_get_currency() {

        $currency = 'GBP';

        if ( kashing_option( 'currency' ) != '' ) {
            $currency = kashing_option( 'currency' );
        }

        return $currency;

    }
}

/**
 * An action to define a redirection when a Kashing payment is complete.
 *
 */

function kashing_redirection_action() {

    // Check if the Response and Reasoncode GET parameters exist in the URL

    if ( isset( $_GET ) && array_key_exists( 'Response', $_GET ) && array_key_exists( 'Reason', $_GET ) ) {

        $return_page = false;

        // Determine the success or failure based on the response and reason code

        if ( $_GET[ 'Response' ] == 1 && $_GET[ 'Reason' ] == 1 && kashing_option( 'success_page' ) != '' ) { // Success
            $return_page = kashing_option( 'success_page' );
        } elseif ( $_GET[ 'Response' ] == 4 ) {
            $return_page = kashing_option( 'failure_page' );
        } elseif ( kashing_option( 'failure_page' ) != '' ) {
            $return_page = kashing_option( 'failure_page' );
        }

        // If redirection page exists, make a redirection.

        if ( $return_page && get_post_status( $return_page ) != false ) {

            $return_url = get_permalink( $return_page );

            // Forward parameters

            $url_parameters = 'kTransactionID=' . $_GET[ 'TransactionID' ] . '&kResponse=' . $_GET[ 'Response' ] . '&kReason=' . $_GET[ 'Reason' ];

            if ( strpos( $return_url, '&' ) !== false ) { // Already has parameters
                $return_url .= '&' . $url_parameters;
            } else {
                $return_url .= '?' . $url_parameters;
            }

            // Make a redirection

            wp_redirect( $return_url );

            exit; // No need to execute the rest of the code.
        }

    }

    return;

}

add_action( 'after_setup_theme', 'kashing_redirection_action' );