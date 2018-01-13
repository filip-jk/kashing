<?php

class Kashing_Ajax {

    public $secret_key;
    public $public_key;
    public $api_url;
    public $prefix;

    function __construct() {

        add_action( 'wp_ajax_call_kashing_ajax', array( $this, 'ajax_process' ) );
        add_action( 'wp_ajax_nopriv_call_kashing_ajax', array( $this, 'ajax_process' ) );

        //shortcodes
        add_action( 'wp_ajax_call_post_types_ajax',  array($this, 'get_post_types')  );
        add_action( 'wp_ajax_nopriv_call_post_types_ajax', array($this, 'get_post_types')  );

        // Keys

        $this->secret_key = 'd708-636c-050e-41ba-4926-dbbe';
        $this->public_key = 'cddf-a1e2-8475-61b4-8ae2-c38a';

        // API URL

        $this->api_url = 'https://development-backend.kashing.co.uk/';

        // Set prefix

        $prefix = Kashing_Payments::$data_prefix;

    }

    function get_post_types() {

        $custom_post_data = array();

        $args = array(
            'post_type' => 'kashing',
            'posts_per_page' => -1
        );

        $the_query = new WP_Query( $args );

        if ($the_query -> have_posts() ) {
            while ( $the_query->have_posts() ) {
                $the_query -> the_post();

                $title = get_the_title();
                $id = get_the_ID();

                array_push($custom_post_data,
                    array(
                        'text' => $title,
                        'value' => strval($id)
                ));
            }

            $response = array(
                'data' => array_reverse($custom_post_data)
            );

            wp_send_json_success( json_encode( $response ) );
        } else {

            wp_send_json_error( 'no posts yet');
        }

        wp_reset_query();
    }

    /**
     * Ajax function added to wp_ajax. Can be called with jQuery AJAX.
     */

    function ajax_process() {

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
            wp_send_json_error( $missing_fields ); // Send an error response if a field is missing (in case JS didn't deal with it)
            return;
        }

        // Full transaction URL

        $url = $this->api_url . 'transaction/init';

        // Gather transaction data

        $merchant_id = kashing_option( 'test_merchant_id' );

        if ( $merchant_id == null ) {
            // Jakieś sprawdzenie wcześniejsze trzeba dodać tj. jeżeli merchant_id nie jest ustawiony w opcjach wtyczki to żeby od razu szedł error, a nie błędne zapytanie było wysyłane do API Kashingu
            $merchant_id = '207';
        }

        $merchant_id = '207';

        // Transaction Amount

        $amount = $this->get_transaction_amount( $form_id );

        if ( $amount == false ) wp_send_json_error( 'No amount is provided in the form.' );

        // Currency

        $currency = kashing_get_currency();

        // Return URL

        if ( kashing_option( 'return_page' ) != '' && get_post_status( kashing_option( 'return_page' ) ) ) {
            $return_url = get_permalink( kashing_option( 'return_page' ) );
        } else {
            $return_url = get_permalink( $form_id ); // If no return page found, we need to redirect somewhere else.
        }

        // Description

        $description = __( "No description", 'kashing' );

        if ( get_post_meta( $form_id, Kashing_Payments::$data_prefix . 'desc', true ) ) {
            $description = get_post_meta( $form_id, Kashing_Payments::$data_prefix . 'desc', true );
        }

        // Array with transaction data

        $transaction_data = array(
            'merchantid' => $merchant_id,
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

        $transaction_psign = $this->get_transaction_psign( $transaction_data );

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
            wp_send_json_error( $response->get_error_message() );
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

    public function get_transaction_psign( $transaction_data_array ) {

        // The transaction string to be hashed: secret key + transaction data string

        $transaction_string = $this->secret_key . $this->extract_transaction_data( $transaction_data_array );

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

}

$kashing_ajax = new Kashing_Ajax();