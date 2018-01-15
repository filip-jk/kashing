<?php

/**
 * Helper function to easily grab a plugin option from the options array.
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
 * Helper function to easily grab a plugin option from the options array.
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
