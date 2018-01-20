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

        $kashing_api = new Kashing_API();

        if ( $kashing_api->has_errors == false && $form_id != '' ) { // There are no configuration errors, display the form.

            ?>

            <form id="kashing-form" class="kashing-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">

                <?php

                $kashing_fields = new Kashing_Fields();
                $form_fields = $kashing_fields->get_all_fields(); // Get all form fields

                foreach ( $form_fields as $field_name => $field_data ) { // Loop through each form field

                    // Check if required

                    $required_attr = '';

                    if ( array_key_exists( 'required', $field_data ) && $field_data[ 'required' ] == true ) {
                        $required = true;
                        $required_attr = ' required';
                    } else {
                        $required = false;
                        $required_attr = '';
                        // Check if the optional field should be displayed (set in the form post meta)

                        if ( get_post_meta( $form_id, $prefix . $field_name, true  ) == false ) {
                            continue; // Do not display the field
                        }

                    }

                    // Get the field type

                    if ( isset( $field_data['type'] ) ) {
                        $field_type = $field_data['type'];
                    } else {
                        $field_type = 'text';
                    }

                    // Check if this is a response from the Admin Post, if so, make a validation

                    $value = $error_class = $error_msg = '';

                    if ( isset( $_GET ) && isset( $_GET[ 'validation_error'] ) ) {

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

                    // Field label

                    echo '<label for="' . esc_attr( $field_id ) . '">' . esc_html( $field_data[ 'label' ] ) . '</label>';

                    // Field input

                    if ( $field_type == 'select' && isset( $field_data['options'] ) ) {

                        echo '<select id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '"' . $required_attr . '>';
                        echo '<option disabled selected value> -- ' . esc_html( 'Select a country', 'kashing' ) . ' -- </option>';

                        // Get options

                        if ( is_array( $field_data['options'] ) ) {
                            $select_options = $field_data['options'];
                        } elseif ( $field_data['options'] == 'countries' ) {
                            $kashing_countries = new Kashing_Countries;
                            $select_options = $kashing_countries->get_all();
                        }

                        // Loop through options

                        foreach ( $select_options as $option_value => $label ) {
                            $selected = ( checked( $value, $option_value, false ) == true ) ? ' selected="selected"' : '';
                            echo '<option value="' . esc_attr( $option_value ) . '"' . $selected . '>' . esc_html( $label ) . '</option>';
                        }

                        echo '</select>';

                    } else {
                        echo '<input type="' . esc_attr( $field_type ) . '" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '" class="kashing-field" value="' . esc_html( $value ) . '"' . $required_attr . '>'; // End <input>
                    }

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

                <button class="button btn kashing-submit-button" type="submit"><?php esc_html_e('Pay with Kashing', 'kashing'); ?></button>
                <?php

                if ( $kashing_api->test_mode == true ) { // Display a little notice regarding the test mode.
                    echo '<span class="kashing-test-mode-notice">' . __( 'Test mode enabled.', 'kashing' ) . '</span>';
                }

                ?>
            </form>

            <?php

        } else {

            echo '<div class="kashing-frontend-errors"><p>';

            if ( current_user_can( 'administrator' ) && isset( $kashing_api->errors ) ) {
                echo '<strong>' . __( 'Kashing Payments plugin configuration errors: ', 'kashing' ) . '</strong>';
                foreach ( $kashing_api->errors as $error ) {
                    if ( isset( $error['msg'] ) ) {
                       echo '<br>' . esc_html( $error[ 'msg' ] );
                    }
                }
                echo '</p><a href="' . esc_url( admin_url( 'edit.php?post_type=kashing&page=kashing-settings' ) ) . '" target="_blank">' . __( 'Visit the plugin settings', 'kashing' ). '</a>';
            } else {
                esc_html_e( 'There are some Kashing Payments plugin configuration errors.', 'kashing' );
            }

            echo '</div>';

        }

        $content = ob_get_contents(); // End content "capture" and store it into a variable.
        ob_end_clean();

        return $content; // Return the shortcode content

    }

    add_shortcode( 'kashing_form', 'kashing_form_shortcode' );

}