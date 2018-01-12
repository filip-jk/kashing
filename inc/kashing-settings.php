<?php

class Kashing_Settings {

    /**
     * Class constructor.
     */

    function __construct() {

        // Add options page

        add_filter( 'mb_settings_pages', array( $this, 'add_options_page' ) );

        // Add options page fields

        add_filter( 'rwmb_meta_boxes', array( $this, 'add_options_page_fields' ) );

    }

    /**
     * Add options page.
     */

    public function add_options_page() {

        $settings_pages[] = array(
            'id'          => 'kashing-settings',
            'option_name' => 'kashing',
            'menu_title'  => __( 'Settings', 'kashing' ),
            'page_title'  => __( 'Kashing Payments Settings', 'kashing' ),
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

    /**
     * Declare Plugin Settings fields.
     */

    public function add_options_page_fields( $meta_boxes ) {

        $kashing_api_key_docs = 'http://kashing.com/docs/how-to-get-api-key.html';

        $meta_boxes[] = array(
            'id'             => 'configuration',
            'title'          => 'API',
            'settings_pages' => 'kashing-settings',
            'tab'            => 'configuration',

            'fields' => array(
                array(
                    'name'    => __( 'Test Mode', 'kashing' ),
                    'desc' => __( 'Activate or deactivate the plugin Test Mode. When Test Mode is activated, no credit card payments are processed.', 'kashing' ) . '<span class="kashing-extra-tip"><a href="' . esc_url( $kashing_api_key_docs ) . '" target="_blank">' . __( 'Retrieve your Kashing API Keys', 'kashing' ) . '</a></span>',
                    'id'      => 'test_mode',
                    'type'    => 'radio',
                    'options' => array(
                        'yes' => __( 'Yes', 'kashing' ),
                        'no' => __( 'No', 'kashing' )
                    ),
                    'std' => 'yes',
                    'inline' => false,
                ),
                // Staging values
                array(
                    'name' => __( 'Test Merchant ID', 'kashing' ),
                    'desc' => __( 'Enter your testing Merchant ID.', 'kashing' ),
                    'id'   => 'test_merchant_id',
                    'type' => 'text',
                    'visible' => array( 'test_mode', '!=', 'no' )
                ),
                array(
                    'name' => __( 'Test Secret Key', 'kashing' ),
                    'desc' => __( 'Enter your testing Kashing Secret Key.', 'kashing' ),
                    'id'   => 'test_skey',
                    'type' => 'text',
                    'visible' => array( 'test_mode', '!=', 'no' )
                ),
                array(
                    'name' => __( 'Test Public Key', 'kashing' ),
                    'desc' => __( 'Enter your testing Kashing Public Key.', 'kashing' ),
                    'id'   => 'test_pkey',
                    'type' => 'text',
                    'visible' => array( 'test_mode', '!=', 'no' )
                ),
                // Live values
                array(
                    'name' => __( 'Live Merchant ID', 'kashing' ),
                    'desc' => __( 'Enter your live Merchant ID.', 'kashing' ),
                    'id'   => 'live_merchant_id',
                    'type' => 'text',
                    'visible' => array( 'test_mode', '=', 'no' )
                ),
                array(
                    'name' => __( 'Live Secret Key', 'kashing' ),
                    'desc' => __( 'Enter your live Kashing Secret Key.', 'kashing' ),
                    'id'   => 'live_skey',
                    'type' => 'text',
                    'visible' => array( 'test_mode', '=', 'no' )
                ),
                array(
                    'name' => __( 'Live Public Key', 'kashing' ),
                    'desc' => __( 'Enter your live Kashing Public Key.', 'kashing' ),
                    'id'   => 'live_pkey',
                    'type' => 'text',
                    'visible' => array( 'test_mode', '=', 'no' )
                ),
            ),
            'validation' => array(
                'rules'  => array(
                    'merchant_id' => array(
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
//                array(
//                    'name' => __( 'Currency', 'kashing' ),
//                    'type' => 'heading',
//                ),
                array(
                    'name' => __( 'Choose Currency', 'kashing' ),
                    'desc' => __( 'Choose a currency for your payments.', 'kashing' ),
                    'id'   => 'currency',
                    'type' => 'select_advanced',
                    'options' => kashing_get_currencies_array()
                ),
                array(
                    'name' => __( 'Return Page', 'kashing' ),
                    'desc' => __( 'Choose the page your clients will be redirected to after the payment is completed.', 'kashing' ),
                    'id'   => 'return_page',
                    'type' => 'select_advanced',
                    'options' => kashing_get_pages_array()
                ),
            ),
        );

        return $meta_boxes;
    }

}

$kashing_settings = new Kashing_Settings();

function kashing_get_currencies_array() {

    $currencies = array(
        'GBP'   => 'GBP',
        'USD'   => 'USD'
    );

    return $currencies;

}

/**
 * Retrieve an array of all pages.
 *
 * @return key => value array
 */

function kashing_get_pages_array() {

    $pages = array();

    $all_pages = get_pages();

    foreach( $all_pages as $page ) {
        $pages[ $page->ID ] = $page->post_title;
    }

    return $pages;

}