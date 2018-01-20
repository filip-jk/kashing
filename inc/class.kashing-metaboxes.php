<?php

require_once KASHING_PATH . 'inc/class.kashing-metabox.php';

/**
 * Responsible for metabox fields in the Kashing Forms custom post type.
 */

class Kashing_Metaboxes {

    /**
     * Class constructor.
     */

    function __construct() {

        $prefix = Kashing_Payments::$data_prefix;

        // Add metaboxes

        $kashing_form_settings = new Kashing_Metabox(
            'kashing_form_settings',
            __( 'Form Settings', 'kashing' ),
            'kashing',
            'normal',
            'high',
            array(
                array(
                    'type' => 'heading',
                    'title' => __( 'General', 'kashing' )
                ),
                array(
                    'title' => __( 'Amount', 'kashing' ),
                    'desc' => __( 'Enter the form amount that will be processed with the payment system.', 'kashing' ),
                    'id' => $prefix . 'amount',
                    'type' => 'text',
                    'validate' => array(
                        'required' => true,
                        'number' => true
                    ),
                ),
                array(
                    'title' => __( 'Description', 'kashing' ),
                    'desc' => __( 'The form transaction description.', 'kashing' ),
                    'id' => $prefix . 'desc',
                    'type' => 'textarea',
                    'validate' => array(
                        'required' => true
                    )
                ),
                array(
                    'type' => 'heading',
                    'title' => __( 'Form Fields', 'kashing' ),
                    'desc' => __( 'Configure the form fields. You may disable fields that are not required by the system.', 'kashing' )
                ),
                array(
                    'id' => $prefix . 'last_name',
                    'title' => __( 'Last Name', 'kashing' ),
                    'desc' => __( 'Enable the "Last Name" field.', 'kashing' ),
                    'type' => 'checkbox',
                    'std' => true,
                ),
                array(
                    'id' => $prefix . 'address2',
                    'title' => __( 'Address 2', 'kashing' ),
                    'desc' => __( 'Enable the "Address 2" field.', 'kashing' ),
                    'type' => 'checkbox',
                ),
                array(
                    'id' => $prefix . 'country',
                    'title' => __( 'Country', 'kashing' ),
                    'desc' => __( 'Enable the "Country" field.', 'kashing' ),
                    'type' => 'checkbox',
                ),
                array(
                    'id' => $prefix . 'email',
                    'title' => __( 'Email', 'kashing' ),
                    'desc' => __( 'Enable the "Email" field.', 'kashing' ),
                    'type' => 'checkbox',
                ),
                array(
                    'id' => $prefix . 'phone',
                    'title' => __( 'Phone', 'kashing' ),
                    'desc' => __( 'Enable the "Phone" field.', 'kashing' ),
                    'type' => 'checkbox'
                )
            )
        );

    }

}

$kashing_metaboxes = new Kashing_Metaboxes();