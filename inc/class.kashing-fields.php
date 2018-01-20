<?php

/**
 * Kashing form fields and validation.
 */

class Kashing_Fields {

    /**
     * The array of all Kashing form fields. Used in various locations.
     *
     * @var array
     */

    private $form_fields;

    /**
     * Class constructor.
     */

    public function __construct() {

        // $form_fields array initialization is in constructor to get access to gettext methods

        $this->form_fields = array(
            'firstname' => array( // Key - field name="" attribute
                'label' => __( 'First Name', 'kashing' ),
                'type' => 'text',
                'required' => true
            ),
            'lastname' => array(
                'label' => __( 'Last Name', 'kashing' ),
                'type' => 'text',
                'required' => true
            ),
            'address1' => array(
                'label' => __( 'Address 1', 'kashing' ),
                'type' => 'text',
                'required' => true
            ),
            'address2' => array(
                'label' => __( 'Address 2', 'kashing' ),
                'type' => 'text'
            ),
            'city' => array(
                'label' => __( 'City', 'kashing' ),
                'type' => 'text',
                'required' => true
            ),
            'country' => array(
                'label' => __( 'Country', 'kashing' ),
                'type' => 'select',
                'options' => 'countries',
                'required' => true
            ),
            'postcode' => array(
                'label' => __( 'Post Code', 'kashing' ),
                'type' => 'text',
                'required' => true
            ),
            'phone' => array(
                'label' => __( 'Phone', 'kashing' ),
                'type' => 'text'
            ),
            'email' => array(
                'label' => __( 'Email', 'kashing' ),
                'type' => 'email' // For now, only 'email' is available along with regular 'text' field type
            )
        );

    }

    /**
     * Return all form fields.
     *
     * @return array
     */

    public function get_all_fields() {
        return $this->form_fields;
    }

    /**
     * Form field validation.
     *
     * @param string
     * @param string
     *
     * @return array
     */

    public function validate_field( $field_name, $value = '' ) {

        $return = array();
        $return[ 'validated' ] = true;

        $field = $this->form_fields[ $field_name ]; // Grab the field specific array

        // If required

        if ( isset( $field[ 'required' ] ) && $field[ 'required' ] == true && $value == '' ) {
            $return[ 'validated' ] = false;
            $return[ 'error_msg' ] = __( 'This field is required.', 'kashing' );
        } elseif ( $value != '' ) { // Additional field type related verification if value is provided

            switch( $field[ 'type' ] ) {
                case 'email':
                    if ( !is_email( $value ) ) {
                        $return[ 'validated' ] = false;
                        $return[ 'error_msg' ] = __( 'Please provide a valid e-mail address.', 'kashing' );
                    }
                    break;

            }

        }

        return $return;

    }

}