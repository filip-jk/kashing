<?php

if ( !function_exists( 'kashing_form_shortcode' ) ) {

    function kashing_form_shortcode( $atts, $content ) {

        extract( shortcode_atts( array(
            "form_id" => '',
        ), $atts ) );

        ob_start(); // We can use HTML directly thanks to this

        ?>

        Tutaj mozna uzywac dowolnie HTML, a takze echo itd

        <?php

        echo 'Event id: ' . $form_id;

        $content = ob_get_contents(); // End content "capture" and store it into a variable.
        ob_end_clean();

        return $content; // Return the shortcode content

    }

    add_shortcode( 'kashing_form', 'kashing_form_shortcode' );

}