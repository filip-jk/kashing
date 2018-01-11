<?php

class Kashing_Options {

    function __construct() {

        // Add options page

        add_filter( 'mb_settings_pages', array( $this, 'add_options_page' ) );

        // Add options page fields

        add_filter( 'rwmb_meta_boxes', array( $this, 'add_options_page_fields' ) );

    }

    // Add Options Page

    public function add_options_page() {

        $settings_pages[] = array(
            'id'          => 'kashing-settings',
            'option_name' => 'kashing',
            'menu_title'  => __( 'Settings', 'kashing' ),
            'icon_url'    => 'dashicons-edit',
            'style'       => 'no-boxes',
            'parent'      => 'edit.php?post_type=kashing',
            'columns'     => 1,
            'tabs'        => array(
                'configuration' => __( 'Configuration', 'kashing' ),
                'general'  => __( 'General', 'kashing' )
            ),
            'position'    => 68,
        );

        return $settings_pages;

    }

    // Add option fields to Options Page

    public function add_options_page_fields( $meta_boxes ) {

        $meta_boxes[] = array(
            'id'             => 'configuration',
            'title'          => 'API',
            'settings_pages' => 'kashing-settings',
            'tab'            => 'configuration',

            'fields' => array(
                array(
                    'name' => __( 'Merchant ID', 'kashing' ),
                    'desc' => __( 'Your merchant ID.', 'kashing' ),
                    'id'   => 'merchant_id',
                    'type' => 'text',
                ),
                array(
                    'name' => __( 'Secret Key', 'kashing' ),
                    'desc' => __( 'Enter your Kashing Secret Key.', 'kashing' ),
                    'id'   => 'skey',
                    'type' => 'text',
                ),
                array(
                    'name' => __( 'Public Key', 'kashing' ),
                    'desc' => __( 'Enter your Kashing Public Key.', 'kashing' ),
                    'id'   => 'pkey',
                    'type' => 'text',
                ),
            ),
            'validation' => array(
                'rules'  => array(
                    'api_key' => array(
                        'required'  => true,
                        'minlength' => 7,
                    ),
                ),
                // Optional override of default error messages
                'messages' => array(
                    'api_key' => array(
                        'required'  => __( 'API Key is required', 'kashing' ),
                        'minlength' => __( 'Password must be at least 7 characters', 'kashing' ),
                    ),
                )
            )
        );

        $meta_boxes[] = array(
            'id'             => 'general',
            'title'          => 'General',
            'settings_pages' => 'kashing-settings',
            'tab'            => 'general',

            'fields' => array(
                array(
                    'name' => __( 'Currency', 'kashing' ),
                    'type' => 'heading',
                ),
                array(
                    'name' => __( 'Choose Currency', 'kashing' ),
                    'id'   => 'currency',
                    'type' => 'select',
                    'options' => kashing_get_currencies_array()
                ),
            ),
        );

        return $meta_boxes;
    }

    /**
     * Extract transaction data values from the transaction data array.
     *
     * @return string
     */

    public static function get_currency() {

        return "GBP";

    }

}

$kashing_option = new Kashing_Options();

function kashing_get_currencies_array() {

    $currencies = array(
        'gbp'   => 'GBP',
        'usd'   => 'USD'
    );

    return $currencies;

}