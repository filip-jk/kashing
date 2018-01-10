<?php

class Kashing_Ajax {

    public $secret_key;
    public $public_key;
    public $api_url;

    function __construct() {

        add_action( 'wp_ajax_call_kashing_ajax', array( $this, 'ajax_process' ) );
        add_action( 'wp_ajax_nopriv_call_kashing_ajax', array( $this, 'ajax_process' ) );

        // Keys

        $this->secret_key = 'd708-636c-050e-41ba-4926-dbbe';
        $this->public_key = 'cddf-a1e2-8475-61b4-8ae2-c38a';

        // API URL

        $this->api_url = 'https://development-backend.kashing.co.uk/';

    }

    /**
     * Ajax function added to wp_ajax. Can be called with jQuery AJAX.
     */

    function ajax_process() {

        // Required form fields (client input)

        $required_form_fields = array( 'firstname', 'lastname', 'address1', 'city', 'postcode', 'country' );
        $firstname = $lastname = $address1 = $city = $postcode = $country = '';

        $missing_fields = array(); // Store missing fields

        // Loop through each required field and check if the value was provided in the form. If yes, assign a new variable

        foreach( $required_form_fields as $form_field_name ) {
            if ( $_REQUEST[ $form_field_name ] == '' ) {
                $missing_fields[] = $form_field_name;
            } else {
                ${ $form_field_name } = $_REQUEST[ $form_field_name ]; // Tworzymy zmienną np. el z tablicy 'firstname' zamieniamy na $firstname
            }
        }

        // If missing fields:

        if ( !empty( $missing_fields ) ) {
            wp_send_json_error( $missing_fields ); // Send an error response if a field is missing (in case JS didn't deal with it)
        }

        // Full transaction URL

        $url = $this->api_url . 'transaction/init';

        // Gather transaction data

        $merchant_id = kashing_option( 'merchant_id' );

        if ( $merchant_id == null ) {
            // Jakieś sprawdzenie wcześniejsze trzeba dodać tj. jeżeli merchant_id nie jest ustawiony w opcjach wtyczki to żeby od razu szedł error, a nie błędne zapytanie było wysyłane do API Kashingu
        }

        // Demo data

//        $firstname = 'Ten';
//        $lastname = 'Green';
//        $address1 = 'Flat 6 Primrose Rise';
//        $city = 'Northampton';
//        $postcode = '12-123';
//        $country = 'UK';

        // Array with transaction data

        $transaction_data = array(
            'merchantid' => $merchant_id,
            'amount' => "100",
            'currency' => "GBP",
            'returnurl' => 'http://veented.com',
            "ip" => "192.168.0.111",
            "forwardedip" => "80.177.11.240",
            "merchanturl" => "shop.test.co.uk",
            "description" => "test description",
            "firstname" => $firstname, // Form
            "lastname" => $lastname, // Form
            "address1" => $address1, // Form
            "city" => $city,
            "postcode" => $postcode,
            "country" => $country
        );

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

}

$kashing_ajax = new Kashing_Ajax();