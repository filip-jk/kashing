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

        // Shortcode output

        ob_start();

        global $kashing_configuration_errors;

        if ( $kashing_configuration_errors != true && $form_id != '' ) { // There are no configuration errors, display the form.

            ?>

            <form id="kashing-form" class="kashing-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">

                <div class="input-holder">
                    <label for="kashing-firstname"><?php esc_html_e('First Name', 'kashing'); ?></label>
                    <input type="text" name="firstname" id="kashing-firstname" class="kashing-required-field" value="Ten">
                </div>

                <div class="input-holder">
                    <label for="kashing-lastname"><?php esc_html_e('Last Name', 'kashing'); ?></label>
                    <input type="text" name="lastname" id="kashing-lastname" class="kashing-required-field" value="Green" required>
                </div>

                <div class="input-holder">
                    <label for="kashing-address1"><?php esc_html_e('Address 1', 'kashing'); ?></label>
                    <input type="text" name="address1" id="kashing-address1" class="kashing-required-field" value="Flat 6 Primrose Rise" required>
                </div>

                <?php

                // Check if the address2 field is enabled in the form meta options.

                if ( get_post_meta($form_id, $prefix . 'address2', true) == true ) {

                    ?>

                    <div class="input-holder">
                        <label for="kashing-address2"><?php esc_html_e('Address 2', 'kashing'); ?></label>
                        <input type="text" name="address2" id="kashing-address2">
                    </div>

                    <?php

                } // End address2 field check

                // Check if the address2 field is enabled in the form meta options.

                if ( get_post_meta($form_id, $prefix . 'email', true) == true ) {

                    ?>

                    <div class="input-holder">
                        <label for="kashing-address2"><?php esc_html_e('Email', 'kashing'); ?></label>
                        <input type="text" name="email" id="kashing-email">
                    </div>

                    <?php

                } // End address2 field check

                // Check if the address2 field is enabled in the form meta options.

                if ( get_post_meta($form_id, $prefix . 'phone', true) == true ) {

                    ?>

                    <div class="input-holder">
                        <label for="kashing-address2"><?php esc_html_e('Phone', 'kashing'); ?></label>
                        <input type="text" name="phone" id="kashing-phone">
                    </div>

                    <?php

                } // End address2 field check

                ?>

                <div class="input-holder">
                    <label for="kashing-city"><?php esc_html_e('City', 'kashing'); ?></label>
                    <input type="text" name="city" id="kashing-city" class="kashing-required-field" value="Northampton" required'>
                </div>

                <div class="input-holder">
                    <label for="kashing-postcode"><?php esc_html_e('Post Code', 'kashing'); ?></label>
                    <input type="text" name="postcode" id="kashing-postcode" class="kashing-required-field" value="12-123" required'>
                </div>

                <div class="input-holder">
                    <label for="kashing-country"><?php esc_html_e( 'Country', 'kashing' ); ?></label>
                    <select name="country" id="kashing-country" class="kashing-required-field" >
                        <?php

                        $kashing_countries = new Kashing_Countries();

                        $countries = $kashing_countries->get_all();


                        foreach( $countries as $country_code => $country_name ) {
                            if($country_code === 'UK') {
                                echo '<option value="' . esc_attr( $country_code ) . '" selected >' . esc_html( $country_name ) . '</option>';
                            } else {
                                echo '<option value="' . esc_attr( $country_code ) . '">' . esc_html( $country_name ) . '</option>';
                            }
                        }

                        ?>
                    </select>
                </div>

                <?php

                // Form nonce

                $kashing_form_nonce = wp_create_nonce( 'kashing_form_nonce' );

                ?>

                <input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>">
                <input type="hidden" name="origin" value="<?php echo get_the_ID(); ?>">
                <input type="hidden" name="kashing_form_nonce" value="<?php echo $kashing_form_nonce; ?>">
                <input type="hidden" name="action" value="kashing_form_submit_hook">

                <button class="button btn" id="kashing-pay" type="submit"><?php esc_html_e('Pay with Kashing', 'kashing'); ?></button>

            </form>

            <?php

        } else {

            echo 'There are errors:';

            $kashing_api = new Kashing_API();

            echo '<pre>';
            var_dump( $kashing_api->errors );
            echo '</pre>';

        }

        $content = ob_get_contents(); // End content "capture" and store it into a variable.
        ob_end_clean();

        return $content; // Return the shortcode content

    }

    add_shortcode( 'kashing_form', 'kashing_form_shortcode' );

}