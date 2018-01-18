<?php

class Kashing_API {

    /**
     * Detect whether the Test Mode is enabled.
     *
     * @var bool
     */

    public $test_mode = true;

    /**
     * The API secret key.
     *
     * @var string
     */

    public $secret_key;

    /**
     * The API public key.
     *
     * @var string
     */

    public $public_key;

    /**
     * Merchant ID.
     *
     * @var string
     */

    public $merchant_id;

    /**
     * The API URL.
     *
     * @var string
     */

    public $api_url;

    /**
     * Errors boolean.
     *
     * @var boolean
     */

    public $has_errors = false;

    /**
     * Errors array.
     *
     * @var array
     */

    public $errors = array();

    /**
     * Class constructor.
     */

    function __construct() {

        // Form Submission Processing

        // regular POST

        add_action( 'admin_post_kashing_form_submit_hook', array( $this, 'action_form_submit' ) );
        add_action( 'admin_post_nopriv_kashing_form_submit_hook', array( $this, 'action_form_submit' ) );

        // AJAX

        add_action( 'wp_ajax_kashing_form_submit_hook', array( $this, 'action_form_submit' ) );
        add_action( 'wp_ajax_nopriv_kashing_form_submit_hook', array( $this, 'action_form_submit' ) );

        // Determine the Test Mode

        $this->init_configuration();

        // Admin notices

        add_action( 'admin_notices', array( $this, 'print_admin_notices' ) );

    }

    /**
     * Assign configuration parameters
     *
     * @return boolean
     */

    public function init_configuration() {

        // Reset error related variables

        $this->has_errors = false;
        $this->errors = array();

        // Determine the Test Mode

        if ( kashing_option( 'test_mode' ) == 'no' ) {
            $this->test_mode = false;
            $option_prefix = 'live_';
            $this->api_url = 'https://development-backend.kashing.co.uk/'; // Live API URL TODO
        } else {
            $option_prefix = 'test_';
            $this->api_url = 'https://development-backend.kashing.co.uk/'; // Dev API URL
        }

        // API Keys

        // Secret key

        $option_name = $option_prefix . 'skey';

        if ( kashing_option( $option_name ) != '' ) {
            $this->secret_key = kashing_option( $option_name );
        } else {
            $this->add_error( array(
                'field' => $option_name,
                'type' => 'missing_field',
                'msg' => __( 'The secret key is missing.', 'kashing' )
            ) );
        }

        // Public Key

//        $option_name = $option_prefix . 'pkey';
//
//        if ( kashing_option( $option_name ) != '' ) {
//            $this->public_key = kashing_option( $option_name );
//        } else {
//            $this->add_error( array(
//                'field' => $option_name,
//                'type' => 'missing_field',
//                'msg' => __( 'The public key is missing.', 'kashing' )
//            ) );
//        }

        // Merchant ID

        $option_name = $option_prefix . 'merchant_id';

        if ( kashing_option( $option_name ) != '' ) {
            $this->merchant_id = kashing_option( $option_name );
        } else { // No merchant ID provided
            $this->add_error( array(
                'field' => $option_name,
                'type' => 'missing_field',
                'msg' => __( 'The merchant ID is missing.', 'kashing' )
            ) );
        }

        // Errors

        global $kashing_configuration_errors; // Store an information about the configuration error globally

        if ( $this->has_errors == false) {
            $kashing_configuration_errors = false; // There are configuration errors
            return true; // Configuration is successful
        }

        // There are errors in the plugin configuration

        $kashing_configuration_errors = true;

        return false;

    }

    /**
     * Errors handler.
     *
     * @param array
     *
     * @return boolean
     */

     public function add_error( $error ) {

        // Check if this is the first error to be added - if so, create an array.

        if ( $this->has_errors == false ) {
            $this->has_errors = true;
        }

        // Add an error to the array.

        if ( is_array( $error) ) {
            $this->errors[] = $error;
            return true;
        }

        return false;

    }

    /**
     * Print admin notices.
     *
     */

    public function print_admin_notices() {

        $this->init_configuration(); // A double check to fix option save action in WordPress

        if ( !is_admin() && $this->has_errors == false ) return false; // Another check, just in case.

        $notice_error_content = '';

        foreach ( $this->errors as $error ) {

            if ( array_key_exists( 'msg', $error ) ) {
                $notice_error_content .= ' ' . $error[ 'msg' ];
            }

        }

        if ( $notice_error_content != '' ) {

            $class = 'notice notice-error';
            $message = __( 'Kashing configuration issues:', 'kashing' ) . ' ' . $notice_error_content;

            printf(
                '<div class="%1$s"><p>%2$s <a href="%4$s">%3$s</a></p></div>',
                esc_attr( $class ), esc_html( $message ),
                esc_html__( 'Visit Plugin Settings', 'kashing' ),
                admin_url( 'edit.php?post_type=kashing&page=kashing-settings' )
            );

        } else {
            return '';
        }

    }

    /**
     * Processing the Kashing form submission, $_POST is available
     */

    function action_form_submit() {

        // Double check if there are configuration errors

        if ( $this->has_errors == true ) {
            if ( current_user_can( 'administrator' ) ) {
                wp_die( __( 'There are some Kashing Payments plugin configuration issues. Please visit the plugin page to learn more.', 'kashing' ) );
            } else {
                wp_die( __( 'Something went wrong. Please contact the site administrator.', 'kashing' ) );
            }
        }

        // Get the form ID

        if ( isset( $_POST[ 'form_id' ] ) ) {

            $form_id = $_POST[ 'form_id' ];

            // Check if form with a given ID exists:

            if ( get_post_status( $form_id ) === false ) {
                if ( current_user_can( 'administrator' ) ) {
                    wp_die( __( 'The form with a given ID in the shortcode does not exist. Please add the [kashing_form] shortcode again.', 'kashing' ) );
                } else {
                    wp_die( __( 'Something went wrong. Please contact the site administrator.', 'kashing' ) );
                }
                return;
            }

        } else { // No form ID provided with the call
            if ( current_user_can( 'administrator' ) ) {
                wp_die( __( 'No form ID was provided in the Kashing Form.', 'kashing' ) );
            } else {
                wp_die( __( 'Something went wrong. Please contact the site administrator.', 'kashing' ) );
            }
            return;
        }

        // Detect if AJAX or regular POST submission

        if ( isset( $_POST[ 'ajaxrequest' ] ) && $_POST[ 'ajaxrequest' ] == true ) {
            $ajax = true;
        } else {
            $ajax = false;
        }

        // Verify Form Nonce

        if ( !isset( $_POST[ 'kashing_form_nonce' ] ) || !wp_verify_nonce( $_POST[ 'kashing_form_nonce' ], 'kashing_form_nonce' ) ) {

            $msg = __( 'Illegal form submission detected.', 'kashing' );

            if ( $ajax == true ) { // Different error handling for AJAX
                wp_send_json_error( $msg );
            } else {
                wp_die( $msg );
            }

            return;

        }

        // Fields array

        $form_fields = array(
            array(
                'name' => 'firstname',
                'required' => true
            ),
            array(
                'name' => 'lastname'
            ),
            array(
                'name' => 'address1',
                'required' => true
            ),
            array(
                'name' => 'address2'
            ),
            array(
                'name' => 'city',
                'required' => true
            ),
            array(
                'name' => 'postcode',
                'required' => true
            ),
            array(
                'name' => 'phone'
            ),
            array(
                'name' => 'email',
                'type' => 'email'
            )
        );

        $field_values = array();
        $validation = true;

        // Fields validation loop

        foreach ( $form_fields as $field ) {

            // If field is required

            $required = false;

            if ( array_key_exists( 'required', $field ) && $field[ 'required' ] == true ) {
                $required = true;
            }

            // Field type

            $field_type = 'text';

            if ( array_key_exists( 'type', $field ) && $field[ 'type' ] == 'email' ) {
                $field_type = 'email';
            }

            // Validate field

            if ( $required == true && ( !isset( $_POST[ $field[ 'name' ] ] ) || isset( $_POST[ $field[ 'name' ] ] ) && $_POST[ $field[ 'name' ] ] == '' ) ) {
                // Field is missing - either not set or empty input value
                $validation = false;
            } elseif ( isset( $_POST[ $field[ 'name' ] ] ) && $_POST[ $field[ 'name' ] ] != '' ) {
                if ( $field_type == 'email' ) {
                    // TODO Additional email field checks
                    $field_values[ $field[ 'name' ] ] = sanitize_email( $_POST[ $field[ 'name' ] ] );
                } else {
                    $field_values[ $field[ 'name' ] ] = sanitize_text_field( $_POST[ $field[ 'name' ] ] );
                }
            }

        }

        // If one of the fields is wrong, validation failed

        if ( $validation == false ) {

            if ( $ajax == true ) { // Different error handling for AJAX
                wp_send_json_error( array(
                    'error_reason' => 'validation' // Repeate the JS validation
                ) );
            } else { // Regular POST

                // Redirect to the form page

                if ( isset( $_POST[ 'origin' ] ) && get_post_status( $_POST[ 'origin' ] ) ) {
                    $redirect_url = esc_url( get_permalink( $_POST[ 'origin' ] ) );

                    // Add form error parameter

                    $redirect_url = add_query_arg( 'validation_error', 'yes', $redirect_url );

                    // Add current field values

                    foreach ( $field_values as $name => $value ) {
                        $redirect_url = add_query_arg( $name, $value, $redirect_url );
                    }

                    // Make a redirection

                    //wp_redirect( $redirect_url );

                } else {
                    wp_die( __( 'There are some missing fields in the form.', 'kashing' ) );
                }

            }

            return;

        }

        // Validation went okay, begin with API Transaction Call

        // Full transaction URL

        $url = $this->api_url . 'transaction/init';

        // Transaction Amount

        $amount = $this->get_transaction_amount( $form_id );

        if ( $amount == false ) wp_send_json_error( 'No amount is provided in the form.' );

        // Currency

        $currency = kashing_get_currency();

        // Return URL

        if ( isset( $_POST[ 'origin' ] ) && get_post_status( $_POST[ 'origin' ] ) ) {
            $return_url = get_permalink( $_POST[ 'origin' ] );
        } else {
            $return_url = get_home_url(); // If no return page found, we need to redirect somewhere else.
        }

        // Description

        if ( get_post_meta( $form_id, Kashing_Payments::$data_prefix . 'desc', true ) ) {
            $description = get_post_meta( $form_id, Kashing_Payments::$data_prefix . 'desc', true );
        } else {
            $description = __( "No description.", 'kashing' );
        }

        // Transaction data array

        $transaction_data = array(
            'merchantid' => sanitize_text_field( $this->merchant_id ),
            'amount' => sanitize_text_field( $amount ),
            'currency' => sanitize_text_field( $currency ),
            'returnurl' => sanitize_text_field( $return_url ),
            "description" => sanitize_text_field( $description )
        );

        // Add form input data to the transaction data array

        $transaction_data = array_merge(
            $transaction_data,
            $field_values
        );

        // Get the transaction psign

        $transaction_psign = $this->get_psign( $transaction_data );

        // Final API Call Body with the psign (merging with the $transaction_data array)

        $final_transaction_array = array(
            'transactions' => array(
                array_merge(
                    $transaction_data,
                    array(
                        'psign' => $transaction_psign
                    )
                )
            )
        );

        // API Call body in JSON Format

        $body = json_encode( $final_transaction_array );

        // Make the API Call

        $response = wp_remote_post(
            $url,
            array(
                'method' => 'POST',
                'timeout' => 10,
                'headers' => array( 'Content-Type' => 'application/json' ),
                'body' => $body,
            )
        );

        // Deal with the call response

        if ( is_wp_error( $response ) ) {
            if ( current_user_can( 'administrator' ) ) {
                wp_die( __( 'There was something wrong with the WordPress API Call.', 'kashing' ) );
            } else {
                wp_die( __( 'Something went wrong. Please contact the site administrator.', 'kashing' ) );
            }
            return;
        }

        // Response is fine

        $response_body = json_decode( $response[ 'body' ] ); // Decode the response body from JSON

        if ( isset( $response_body->error ) && isset( $response_body->responsecode ) ) {

            if ( $response_body->responsecode == 1 && isset( $response_body->results ) && isset( $response_body->results[0] ) && isset( $response_body->results[0]->responsecode ) && isset( $response_body->results[0]->reasoncode ) ) {

                if ( $response_body->results[0]->responsecode == 4 && $response_body->results[0]->reasoncode == 1 && isset( $response_body->results ) && isset( $response_body->results[0]->redirect ) ) { // We've got a redirection

                    // Everything is fine, redirecting the user

                    $redirect_url = $response_body->results[0]->redirect; // Kashing redirect URL

                    $ajax_response = array(
                        'action' => 'redirect',
                        'redirect_url' => $redirect_url
                    );

                    if ( $ajax == true ) { // Different approach for AJAX
                        wp_send_json_success( $ajax_response ); // Tell JS to make a redirection.
                    } else { // Regular POST
                        wp_redirect( esc_url( $redirect_url ) ); // Redirect to the Kashing Payment Gateway.
                    }

                    return;

                } else { // There is no Redirect URL

                    if ( current_user_can( 'administrator' ) ) {
                        wp_die( __( 'There was something wrong with a redirection response from the Kashing server.', 'kashing' ) );
                    } else {
                        wp_die( __( 'Something went wrong. Please contact the site administrator.', 'kashing' ) );
                    }

                    return;
                }

            }

            // There was an error

            if ( current_user_can( 'administrator' ) ) {

                // We're going to display the site administrator as many details as possible

                $response_msg = __( 'There was an error with the Kashing API call', 'kashing' ) . ':<br>';
                $response_msg .= '<br><strong>Response Code:</strong> ' . $response_body->responsecode;
                $response_msg .= '<br><strong>Reason Code:</strong> ' . $response_body->reasoncode;
                $response_msg .= '<br><strong>Error:</strong> ' . $response_body->error;

                // Additional suggestion based on the error type

                $suggestion = $this->get_api_error_suggestion( $response_body->responsecode, $response_body->reasoncode );

                if ( $suggestion != false ) {
                    $response_msg .= '<br><strong>' . __( 'Suggestion', 'kashing' ) . ':</strong> ' . $suggestion;
                }

                // Add plugin URL

                $response_msg .= '<br><br><a href="' . esc_url( admin_url( 'edit.php?post_type=kashing&page=kashing-settings' ) ) . '">' . __( 'Visit Plugin Settings', 'kashing' ). '</a>';

                // Display a full response to the site admin

                wp_die( $response_msg );

            } else {
                wp_die( __( 'Something went wrong. Please contact the site administrator.', 'kashing' ) );
            }

            return;

        }

        wp_die( __( 'There was something wrong with the Kashing response.', 'kashing' ) );

        return;

    }

    /**
     * Additional suggestion for the plugin administrator based on the response and reason code from Kashing API.
     *
     * @param int
     * @param int
     *
     * @return string
     */

    public function get_api_error_suggestion( $response_code, $reason_code ) {

        if ( $response_code == 3 ) {
            switch ( $reason_code ) {
                case 9:
                    return __( 'Please make sure your Merchant ID is correct.', 'kashing' );
                    break;
                case 104:
                    return __( 'Please make sure that your Secret API Key and Merchant ID are correct.', 'kashing' );
                    break;
            }
        }

        return '';

    }

    /**
     * Ajax function added to wp_ajax. Can be called with jQuery AJAX.
     *
     * @return string
     */

    public function get_psign( $data_array ) {

        // The transaction string to be hashed: secret key + transaction data string

        $transaction_string = $this->secret_key . $this->extract_transaction_data( $data_array );

        // SHA1

        $psign = sha1( $transaction_string );

        return $psign;

    }

    /**
     * Extract transaction data values from the transaction data array.
     *
     * @return string
     */

    public function extract_transaction_data( $transaction_data_array ) {

        $data_string = '';

        foreach ( $transaction_data_array as $data_key => $data_value ) {
            $data_string .= $data_value;
        }

        return $data_string;

    }

    /**
     * Get the transaction amount from the form meta settings.
     *
     * @return string
     */

    public function get_transaction_amount( $form_id ) {

        if ( get_post_meta( $form_id, Kashing_Payments::$data_prefix . 'amount', true ) != '' ) {
            $amount = get_post_meta( $form_id, Kashing_Payments::$data_prefix . 'amount', true );

            if ( is_int( $amount ) ) {
                $amount = $amount*100; // User typed 100 and expects it to be $100 and not $1.00
                return $amount;
            } elseif ( is_numeric( $amount ) ) {
                return $amount;
            }

        }

        return false;

    }

    /**
     * Make an API call for more details about the payment failure.
     *
     * @param string
     *
     * @return array
     */

    public function api_get_transaction_error_details( $transaction_id, $uid = null) {

        // Full API Call URL

        $url = $this->api_url . 'json/transaction/find';
        //$url = $this->api_url . 'transaction/retrieve';

        // Call data

        $merchant_id = kashing_option( 'test_merchant_id' ); // Merchant ID

        // Call data array

        $data_array = array(
            'MerchantID' => $this->merchant_id,
            'TransactionID' => $transaction_id
        );

        // TODO: Add UID if exists

        // if ( $uid = null ) $uid = ''; // Optional parameter
        // 'uid' => $uid

        // Psign

        $call_psign = $this->get_psign( $data_array );

        // Final API Call Body with the psign (merging with the $transaction_data array)

        $final_data_array = array_merge(
            $data_array,
            array(
                'pSign' => $call_psign
            )
        );

        // Encode the final transaction array to JSON

        $body = json_encode( $final_data_array );

        print_r( $body ); // TODO Remove

        // Make the API Call

        $response = wp_remote_post(
            $url,
            array(
                'method' => 'POST',
                'timeout' => 20,
                'headers' => array( 'Content-Type' => 'application/json' ),
                'body' => $body,
            )
        );

        // Deal with the API response

        if ( is_wp_error( $response ) ) {
            //wp_send_json_error( $response );
        } else {
            return $response;
        }

//        echo '<pre>';
//        var_dump( $response );
//        echo '</pre>';

        return;

    }


}

$kashing_ajax = new Kashing_API();