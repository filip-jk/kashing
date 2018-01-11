<?php

class Kashing_Metaboxes {

    /**
     * Class constructor.
     */

    function __construct() {

        // Load Meta-Box Core

        require_once KASHING_PATH . 'inc/metabox-core/meta-box/meta-box.php';
        require_once KASHING_PATH . 'inc/metabox-core/extensions/mb-settings-page/mb-settings-page.php';

        // Add Kashing Post Type Metaboxes

        $this->add_metaboxes();

    }

    /**
     * Add filter to rwmb_meta_boxes for metaboxes.
     */

    function add_metaboxes() {
        add_filter( 'rwmb_meta_boxes', array( $this, 'filter_add_metaboxes' ) );
    }

    /**
     * Filter with metabox declarations.
     */

    function filter_add_metaboxes( $meta_boxes ) {

        $prefix = Kashing_Payments::$data_prefix;

        $meta_boxes[] = array(
            'id' => 'untitled',
            'title' => esc_html__( 'Kashing Form Settings', 'kashing' ),
            'post_types' => array( 'kashing' ),
            'context' => 'advanced',
            'priority' => 'high',
            'autosave' => false,
            'fields' => array(
                array(
                    'id' => $prefix . 'amount',
                    'type' => 'text',
                    'name' => esc_html__( 'Amount', 'kashing' ),
                    'desc' => esc_html__( 'Dodatkowy opis', 'kashing' ),
                ),
                array(
                    'id' => $prefix . 'name',
                    'name' => esc_html__( 'Name', 'kashing' ),
                    'type' => 'checkbox',
                    'std' => true,
                ),
                array(
                    'id' => $prefix . 'last_name',
                    'name' => esc_html__( 'Last Name', 'kashing' ),
                    'type' => 'checkbox',
                    'std' => true,
                ),
                array(
                    'id' => $prefix . 'address1',
                    'name' => esc_html__( 'Address 1', 'kashing' ),
                    'type' => 'checkbox',
                ),
            ),
            'validation' => array(
                'rules'  => array(
                    $prefix . 'amount' => array(
                        'required'  => true,
                        'minlength' => 1,
                    ),
                ),
                // Optional override of default error messages
                'messages' => array(
                    $prefix . 'amount' => array(
                        'required'  => 'API Key is required',
                        'minlength' => 'Password must be at least 7 characters',
                    ),
                )
            )
        );

        return $meta_boxes;

    }

}

$kashing_metaboxes = new Kashing_Metaboxes();