<?php

if ( !function_exists( 'kashing_form_shortcode' ) ) {

    function kashing_form_shortcode( $atts, $content ) {

        $form_id = '';

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

                <?php

                $kashing_fields = new Kashing_Fields();
                $form_fields = $kashing_fields->get_all_fields(); // Get all form fields

                foreach ( $form_fields as $field_name => $field_data ) { // Loop through each form field

                    // Check if required

                    if ( array_key_exists( 'required', $field_data ) && $field_data[ 'required' ] == true ) {
                        $required = true;
                    } else {
                        $required = false;

                        // Check if the optional field should be displayed (set in the form post meta)

                        if ( get_post_meta( $form_id, $prefix . $field_name, true  ) == false ) {
                            continue; // Do not display the field
                        }

                    }

                    // Get the field type

                    if ( array_key_exists( 'type', $field_data ) && $field_data[ 'type' ] == 'email' ) {
                        $field_type = 'email';
                    } else {
                        $field_type = 'text';
                    }

                    // Check if this is a response from the Admin Post, if so, make a validation

                    $value = $error_class = $error_msg = '';

                    if ( isset( $_GET ) && isset( $_GET[ 'validation_error'] ) ) {
                        //$value = $_GET[ $field[ 'name' ] ]; // TODO MAKE VALIDATION

                        if ( isset( $_GET[ $field_name ] ) ) {
                            $value = $_GET[ $field_name ];
                        }

                        $validate = $kashing_fields->validate_field( $field_name, $value ); // Returns an array

                        if ( $validate[ 'validated' ] == false ) { // There is a validation error
                            $error_class = ' validation-error';
                            $error_msg = $validate[ 'error_msg' ];
                        }

                    }

                    // Field output

                    $field_id = 'kashing-' . $field_name;

                    echo '<div class="input-holder' . $error_class . '">';
                    echo '<label for="' . esc_attr( $field_id ) . '">' . esc_html( $field_data[ 'label' ] ) . '</label>';
                    echo '<input type="' . esc_attr( $field_type ) . '" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '" class="kashing-field" value="' . esc_html( $value ) . '"';
                    if ( $required == true ) echo ' required';
                    echo '>'; // End <input>
                    if ( $error_msg != '' ) echo '<div class="kashing-form-error-msg">' . esc_html( $error_msg ) . '</div>';
                    echo '</div>';

                }

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