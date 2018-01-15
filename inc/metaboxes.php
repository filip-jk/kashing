<?php

if ( class_exists( 'RWMB_Field' ) ) {

	class RWMB_Amount_Field extends RWMB_Field {


		public static function html( $meta, $field ) {
			return sprintf(
				'<input type="number" name="%s" id="%s" value="%s" pattern="[0-9]+([\.,][0-9]+)?">',
				$field['field_name'],
				$field['id'],
				$meta
			);
		}

	}
}

class Kashing_Metaboxes {

    /**
     * Class constructor.
     */

    function __construct() {

        // Load Meta-Box Core

        require_once KASHING_PATH . 'inc/metabox-core/meta-box/meta-box.php';
        require_once KASHING_PATH . 'inc/metabox-core/extensions/mb-settings-page/mb-settings-page.php';
        require_once KASHING_PATH . 'inc/metabox-core/extensions/meta-box-conditional-logic/meta-box-conditional-logic.php';

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
                    'type' => 'heading',
                    'name' => __( 'General', 'kashing' ),
                ),
                array(
                    'name' => __( 'Amount', 'kashing' ),
                    'desc' => __( 'Enter the form amount that will be processed with the payment system.', 'kashing' ),
                    'id' => $prefix . 'amount',
                    'type' => 'text'
                ),
                array(
                    'name' => __( 'Description', 'kashing' ),
                    'desc' => __( 'The form transaction description.', 'kashing' ),
                    'id' => $prefix . 'desc',
                    'type' => 'textarea'
                ),
                array(
                    'type' => 'heading',
                    'name' => __( 'Form Fields', 'kashing' ),
                    'desc' => __( 'Configure the form fields. You may disable fields that are not required by the system.', 'kashing' )
                ),
                array(
                    'id' => $prefix . 'last_name',
                    'name' => __( 'Last Name', 'kashing' ),
                    'desc' => __( 'Enable the "Last Name" field.', 'kashing' ),
                    'type' => 'checkbox',
                    'std' => true,
                ),
                array(
                    'id' => $prefix . 'address2',
                    'name' => __( 'Address 2', 'kashing' ),
                    'desc' => __( 'Enable the "Address 2" field.', 'kashing' ),
                    'type' => 'checkbox',
                ),
                array(
                    'id' => $prefix . 'email',
                    'name' => __( 'Email', 'kashing' ),
                    'desc' => __( 'Enable the "Email" field.', 'kashing' ),
                    'type' => 'checkbox',
                ),
                array(
                    'id' => $prefix . 'phone',
                    'name' => __( 'Phone', 'kashing' ),
                    'desc' => __( 'Enable the "Phone" field.', 'kashing' ),
                    'type' => 'checkbox'
                )
            ),
            'validation' => array(
                'rules'  => array(
                    $prefix . 'amount' => array(
                        'required'  => true,
                        'maxlength' => 20,
                        'number' => true
                    ),
                    $prefix . 'desc' => array(
                        'required'  => true,
                        'maxlength' => 500
                    ),
                ),
                // Optional override of default error messages
                'messages' => array(
                    $prefix . 'amount' => array(
                        'required'  => __( 'Form amount is required', 'kashing' )
                    ),
                )
            )
        );

        return $meta_boxes;

    }

}

$kashing_metaboxes = new Kashing_Metaboxes();