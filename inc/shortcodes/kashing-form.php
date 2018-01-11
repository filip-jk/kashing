<?php

if ( !function_exists( 'kashing_form_shortcode' ) ) {

    function kashing_form_shortcode( $atts, $content ) {

        extract( shortcode_atts( array(
            "form_id" => '',
        ), $atts ) );

        // Quick checks

        if ( $form_id == '' ) { // No form is selected
            return esc_html( 'No Kashing Form was selected.', 'kashing' );
        } elseif ( get_post_status( $form_id ) === false ) { // Form doesn't exist
            return esc_html( 'Selected Kashing Form does not exist.', 'kashing' );
        }

        // Enqueue form related scripts and styles

        wp_enqueue_script( 'kashing-frontend-css' );
        wp_enqueue_style( 'kashing-frontend-js' );

        // Get prefix

        $prefix = Kashing_Payments::$data_prefix;

        ob_start(); // We can use HTML directly thanks to this

        ?>

        <form class="kashing-form">

            <div class="input-holder">
                <label for="kashing-firstname"><?php esc_html_e( 'First Name', 'kashing' ); ?></label>
                <input type="text" name="firstname" id="kashing-firstname" class="kashing-required-field" value="Ten">
            </div>

            <div class="input-holder">
                <label for="kashing-lastname"><?php esc_html_e( 'Last Name', 'kashing' ); ?></label>
                <input type="text" name="lastname" id="kashing-lastname" class="kashing-required-field" value="Green">
            </div>

            <div class="input-holder">
                <label for="kashing-address1"><?php esc_html_e( 'Address 1', 'kashing' ); ?></label>
                <input type="text" name="address1" id="kashing-address1" class="kashing-required-field" value="Flat 6 Primrose Rise">
            </div>

            <?php

            // Check if the address2 field is enabled in the form meta options.

            if ( get_post_meta( $form_id, $prefix . 'address2', true ) == true ) {

            ?>

            <div class="input-holder">
                <label for="kashing-address2"><?php esc_html_e( 'Address 2', 'kashing' ); ?></label>
                <input type="text" name="address2" id="kashing-address2" value>
            </div>

            <?php

            } // End address2 field check

            ?>

            <div class="input-holder">
                <label for="kashing-city"><?php esc_html_e( 'City', 'kashing' ); ?></label>
                <input type="text" name="city" id="kashing-city" class="kashing-required-field" value="Northampton">
            </div>

            <div class="input-holder">
                <label for="kashing-postcode"><?php esc_html_e( 'Post Code', 'kashing' ); ?></label>
                <input type="text" name="postcode" id="kashing-postcode" class="kashing-required-field" value="12-123">
            </div>

            <div class="input-holder">
                <label for="kashing-country"><?php esc_html_e( 'Country', 'kashing' ); ?></label>
                <select name="country" id="kashing-country" class="kashing-required-field">
                    <?php

                    $countries = array(
                        'UK' => 'United Kingdom',
                        'US' => 'United States'
                    );

                    foreach( $countries as $country_code => $country_name ) {
                        echo '<option value="' . esc_attr( $country_code ) . '">' . esc_html( $country_name ) . '</option>';
                    }

                    ?>
                </select>
            </div>

            <input type="hidden" id="kashing-form-id" value="<?php echo esc_attr( $form_id ); ?>">

            <button class="button btn" id="kashing-pay" type="button"><?php esc_html_e('Pay with Kashing', 'kashing' ); ?></button>

        </form>

        <?php

        $content = ob_get_contents(); // End content "capture" and store it into a variable.
        ob_end_clean();

        return $content; // Return the shortcode content

    }

    add_shortcode( 'kashing_form', 'kashing_form_shortcode' );

}