<?php

class Kashing_Currency {

    private $currencies;
    public $currency_symbols;
    private $currency_data_path;

    /**
     * Constructor class
     */

    function __construct() {

        $this->init_currency_symbols();
        $this->init_currencies();

        $this->currency_data_path = KASHING_PATH . 'inc/helpers/currency-misc/';

    }

    /**
     * Get all currencies.
     *
     * @return array
     */

    function get_all() {

        return $this->currencies;

    }

    /**
     * Get currency name by ISO Code
     *
     * @param string
     *
     * @return string
     */

    function get_name( $iso_code ) {

        if ( array_key_exists( $iso_code, $this->currencies ) ) {
            return $this->currencies[ $iso_code ][0];
        }

        return null; // Currency does not have a symbol

    }

    /**
     * Get the currency symbol by ISO Code
     *
     * @param string
     *
     * @return string
     */

    function get_currency_symbol( $iso_code ) {

        if ( array_key_exists( $iso_code, $this->currency_symbols ) ) {
            return $this->currency_symbols[ $iso_code ];
        }

        return false; // Currency does not have a symbol

    }

    /**
     * Get the array of currency symbols
     *
     * @return array
     */

    function get_currency_symbols_array() {

        return $this->currency_symbols;

    }

    /**
     * Assign currency array to the $currencies variable.
     *
     * @return void
     */

    function init_currencies() {

        $file = KASHING_PATH . 'inc/helpers/currency-misc/currency-list.php'; // A full list of currencies

        if ( is_file( $file ) ) {

            // Get the currency list array from the file

            $currency_list_array = include $file;

            $new_currency_array = array();

            // Prepare data
            if ( is_array( $currency_list_array ) ) {

                foreach( $currency_list_array as $code => $data ) {

                    $currency_symbol = '';

                    if ( ( $symbol = $this->get_currency_symbol( $code ) ) ) {
                        $currency_symbol = ' (' . $symbol . ')';
                    } else {
                        $currency_symbol = ' (' . $code . ')';
                    }

                    $new_currency_array[ $code ] = $data[0] . $currency_symbol;
                }

                $this->currencies = $new_currency_array;

            }

        }

        return;

    }

    /**
     * Assign currency symbols array to the variable.
     *
     * @return void
     */

    function init_currency_symbols() {

        $file = KASHING_PATH . 'inc/helpers/currency-misc/currency-symbols.php'; // A full list of currencies

        if ( is_file( $file ) ) {

            // Get the currency list array from the file

            $currency_list_array = include $file;

            if ( is_array( $currency_list_array ) ) {
                $this->currency_symbols = $currency_list_array;
            }

        }

        return;

    }



}