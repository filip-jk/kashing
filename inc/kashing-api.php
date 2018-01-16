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

        // The main transaction API call

        add_action( 'wp_ajax_call_kashing_post_transaction', array( $this, 'ajax_api_process_transaction' ) );
        add_action( 'wp_ajax_nopriv_call_kashing_post_transaction', array( $this, 'ajax_api_process_transaction' ) );

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

        $option_name = $option_prefix . 'pkey';

        if ( kashing_option( $option_name ) != '' ) {
            $this->public_key = kashing_option( $option_name );
        } else {
            $this->add_error( array(
                'field' => $option_name,
                'type' => 'missing_field',
                'msg' => __( 'The public key is missing.', 'kashing' )
            ) );
        }

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
     * Ajax function added to wp_ajax. Can be called with jQuery AJAX.
     */

    function ajax_api_process_transaction() {

        // Get form ID

        if ( isset( $_REQUEST[ 'form_id' ] ) ) {

            $form_id = $_REQUEST[ 'form_id' ];

            // Check if form with a given ID exists:

            if ( get_post_status( $form_id ) === false ) {
                wp_send_json_error( 'form doesnt exist' );
                return;
            }

        } else { // No form ID provided with the call
            wp_send_json_error( 'no form id given' );
            return;
        }

        // Required form fields (client input)

        $required_form_fields = array( 'firstname', 'address1', 'city', 'postcode', 'country' );
        $firstname = $address1 = $city = $postcode = $country = '';

        $missing_fields = array(); // Store missing fields

        // Loop through each required field and check if the value was provided in the form. If yes, assign a new variable

        foreach( $required_form_fields as $form_field_name ) {
            if ( $_REQUEST[ $form_field_name ] == '' ) {
                $missing_fields[] = $form_field_name;
            } else {
                ${ $form_field_name } = $_REQUEST[ $form_field_name ]; // Tworzymy zmienną np. el z tablicy 'firstname' zamieniamy na $firstname
            }
        }

        $text = '';
        $text = esc_attr( $text );
        // echo : Bla

        // If missing fields:

        if ( !empty( $missing_fields ) ) {
            wp_send_json_error( array(
               'error_type' => 'missing_fields',
               'missing_fields' => $missing_fields
            ) ); // Send an error response if a field is missing (in case JS didn't deal with it)
            return;
        }

        // Full transaction URL

        $url = $this->api_url . 'transaction/init';

        // Gather transaction data

        // Transaction Amount

        $amount = $this->get_transaction_amount( $form_id );

        if ( $amount == false ) wp_send_json_error( 'No amount is provided in the form.' );

        // Currency

        $currency = kashing_get_currency();

        // Return URL

        if ( isset( $_REQUEST[ 'page_id' ] ) && get_post_status( $_REQUEST[ 'page_id' ] ) ) {
            $return_url = get_permalink( $_REQUEST[ 'page_id' ] );
        } else {
            $return_url = get_home_url(); // If no return page found, we need to redirect somewhere else.
        }

        // Description

        $description = __( "No description", 'kashing' );

        if ( get_post_meta( $form_id, Kashing_Payments::$data_prefix . 'desc', true ) ) {
            $description = get_post_meta( $form_id, Kashing_Payments::$data_prefix . 'desc', true );
        }

        // Array with transaction data

        $transaction_data = array(
            'merchantid' => $this->merchant_id,
            'amount' => $amount,
            'currency' => $currency,
            'returnurl' => esc_url( $return_url ),
            "ip" => "192.168.0.111",
            "forwardedip" => "80.177.11.240",
            "merchanturl" => "shop.test.co.uk",
            "description" => esc_textarea( $description ),
            "firstname" => esc_textarea( $firstname ), // Form
            "address1" => esc_textarea( $address1 ), // Form
            "city" => esc_html( $city ),
            "postcode" => esc_html( $postcode ),
            "country" => esc_html( $country )
        );

        // Optional fields

        // Address 2

        $optional_field_names = array( 'address2', 'lastname', 'phone', 'email' );
        $optional_fields = array();

        foreach( $optional_field_names as $field_name ) {
            if ( $_REQUEST[ $field_name ] != '' ) {
                $optional_fields[ $field_name ] = $_REQUEST[ $field_name ];
            }
        }

        // If any optional field was typed, make a merge with the $transaction_data

        if ( !empty( $optional_fields ) ) {
            $transaction_data = array_merge(
                $transaction_data,
                $optional_fields
            );
        }

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

        // Encode the final transaction array to JSON

        $body = json_encode( $final_transaction_array );
        //wp_send_json_error( $final_transaction_array ); // See the entire call body

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
            wp_send_json_error( $response->get_error_message() ); // 'error_type' => 'api_call_error'
        } else {
            //wp_send_json_success( $response );
        }

        $response_body = json_decode( $response['body'] ); // Decode the response body from JSON

        $response_code = $reason_code = false;

        $final_response = array();

        if ( is_object( $response_body ) && is_array( $response_body->results ) && !empty( $response_body->results ) ) {

            // Get response and reason codes

            $response_code = $response_body->results[0]->responsecode;
            $reason_code = $response_body->results[0]->reasoncode;

            $final_response = array(
                'responsecode' => $response_code,
                'reasoncode' => $reason_code,
                'merchant_id' => kashing_option( 'merchant_id' )
            );

            // Check response and reason codes

            if ( $response_code == 4 && $reason_code == 1 ) {
                $redirect_url = $response_body->results[0]->redirect;
                $final_response[ 'redirect' ] = $redirect_url;
            }

            // Send a JSON response with required data

            wp_send_json_success( json_encode( $final_response ) );

        } else {
            wp_send_json_error( 'Error: No response and reason code' );
        }

        $return_value = 'your success message/data' ;

        wp_send_json_success ($return_value) ;

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