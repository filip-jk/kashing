<?php

if ( !function_exists( 'kashing_form_shortcode' ) ) {

    function kashing_form_shortcode( $atts, $content ) {

        extract( shortcode_atts( array(
            "form_id" => '',
        ), $atts ) );

        ob_start(); // We can use HTML directly thanks to this

        // Zapytanie do API

        echo 'Bla:<br><br><br>';

        $url = 'https://development-backend.kashing.co.uk/transaction/init';

        $array = array(
            'transactions' => array(array(
                'merchantid' => "207",
                'amount' => "100",
                'currency' => "GBP",
                'returnurl' => 'http://veented.com',
                "ip" => "192.168.0.111",
                "forwardedip" => "80.177.11.240",
                "merchanturl" => "shop.test.co.uk",
                "description" => "test description",
                "firstname" => "Ten",
                "lastname" => "Green",
                "address1" => "Flat 6 Primrose Rise",
                "city" => "Northampton",
                "postcode" => "12-123",
                "country" => "UK",
                "psign" => "7bda80def2391a02891970db5c5f2c58ccd4596b"
            ))
        );

        $body = json_encode( $array );

        print_r( $body ); // Wypisujemy BODY zeby zobaczyc jak wyglada

        $response = wp_remote_post(
            $url,
            array(
                'method' => 'POST',
                'timeout' => 10,
                'headers' => array( 'Content-Type' => 'application/json' ),
                'body' => $body,
            )
        );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            echo 'Response:<pre>';
            print_r( $response );
            echo '</pre>';
        }

        echo 'Event id: ' . $form_id; // takie tam pierdoly

        $content = ob_get_contents(); // End content "capture" and store it into a variable.
        ob_end_clean();

        return $content; // Return the shortcode content

    }

    add_shortcode( 'kashing_form', 'kashing_form_shortcode' );

}